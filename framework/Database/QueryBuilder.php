<?php

declare(strict_types=1);

namespace Framework\Database;

use InvalidArgumentException;

/**
 * Fluent SQL builder. Values are always bound as parameters; identifiers are validated and quoted.
 *
 *   $db->table('items')->where('value', 'like', '%foo%')->orderBy('id', 'desc')->limit(10)->get();
 */
final class QueryBuilder
{
    private const OPERATORS = ['=', '!=', '<>', '<', '>', '<=', '>=', 'LIKE', 'NOT LIKE'];

    /** @var list<string> */
    private array $columns = ['*'];

    /** @var list<array{boolean: string, sql: string, bindings: list<mixed>}> */
    private array $wheres = [];

    /** @var list<string> */
    private array $orders = [];

    private ?int $limit = null;

    private ?int $offset = null;

    public function __construct(private readonly Connection $db, private readonly string $table)
    {
    }

    public function select(string ...$columns): self
    {
        $this->columns = $columns ?: ['*'];

        return $this;
    }

    /**
     * where('id', 5) is shorthand for where('id', '=', 5).
     */
    public function where(string $column, mixed $operator, mixed $value = null, string $boolean = 'AND'): self
    {
        if (func_num_args() === 2) {
            [$value, $operator] = [$operator, '='];
        }

        $operator = strtoupper((string) $operator);

        if (!in_array($operator, self::OPERATORS, true)) {
            throw new InvalidArgumentException("Invalid operator [$operator].");
        }

        if ($value === null) {
            return $operator === '=' ? $this->whereNull($column, $boolean) : $this->whereNotNull($column, $boolean);
        }

        $value = $value instanceof \BackedEnum ? $value->value : $value;

        return $this->addWhere($boolean, $this->db->quoteIdentifier($column) . " $operator ?", [$value]);
    }

    public function orWhere(string $column, mixed $operator, mixed $value = null): self
    {
        return func_num_args() === 2
            ? $this->where($column, '=', $operator, 'OR')
            : $this->where($column, $operator, $value, 'OR');
    }

    public function whereIn(string $column, array $values, string $boolean = 'AND'): self
    {
        if ($values === []) {
            return $this->addWhere($boolean, '1 = 0', []);
        }

        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        return $this->addWhere($boolean, $this->db->quoteIdentifier($column) . " IN ($placeholders)", array_values($values));
    }

    public function whereNull(string $column, string $boolean = 'AND'): self
    {
        return $this->addWhere($boolean, $this->db->quoteIdentifier($column) . ' IS NULL', []);
    }

    public function whereNotNull(string $column, string $boolean = 'AND'): self
    {
        return $this->addWhere($boolean, $this->db->quoteIdentifier($column) . ' IS NOT NULL', []);
    }

    /**
     * Match rows where any of the columns contains the term: (a LIKE ? OR b LIKE ?).
     */
    public function search(array $columns, string $term): self
    {
        $term = trim($term);

        if ($term === '' || $columns === []) {
            return $this;
        }

        $like = '%' . addcslashes($term, '%_\\') . '%';
        $parts = array_map(fn (string $column) => $this->db->quoteIdentifier($column) . " LIKE ? ESCAPE '\\'", $columns);

        return $this->addWhere('AND', '(' . implode(' OR ', $parts) . ')', array_fill(0, count($columns), $like));
    }

    public function orderBy(string $column, string $direction = 'asc'): self
    {
        $direction = strtoupper($direction) === 'DESC' ? 'DESC' : 'ASC';
        $this->orders[] = $this->db->quoteIdentifier($column) . ' ' . $direction;

        return $this;
    }

    public function latest(string $column = 'id'): self
    {
        return $this->orderBy($column, 'desc');
    }

    public function limit(int $limit): self
    {
        $this->limit = max(0, $limit);

        return $this;
    }

    public function offset(int $offset): self
    {
        $this->offset = max(0, $offset);

        return $this;
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function get(): array
    {
        return $this->db->select($this->toSql(), $this->getBindings());
    }

    public function first(): ?array
    {
        return (clone $this)->limit(1)->get()[0] ?? null;
    }

    public function find(int $id): ?array
    {
        return (clone $this)->where('id', $id)->first();
    }

    public function value(string $column): mixed
    {
        return (clone $this)->select($column)->first()[$column] ?? null;
    }

    public function count(): int
    {
        $query = clone $this;
        $query->orders = [];
        $query->limit = $query->offset = null;

        $row = $this->db->selectOne(
            'SELECT COUNT(*) AS aggregate FROM ' . $this->db->quoteIdentifier($this->table) . $query->compileWheres(),
            $query->getBindings(),
        );

        return (int) ($row['aggregate'] ?? 0);
    }

    public function sum(string $column): float
    {
        $row = $this->db->selectOne(
            'SELECT SUM(' . $this->db->quoteIdentifier($column) . ') AS aggregate FROM ' . $this->db->quoteIdentifier($this->table) . $this->compileWheres(),
            $this->getBindings(),
        );

        return (float) ($row['aggregate'] ?? 0);
    }

    public function exists(): bool
    {
        return $this->count() > 0;
    }

    /**
     * @return array{rows: list<array<string, mixed>>, total: int, page: int} The page is clamped to the valid range.
     */
    public function paginateRows(int $perPage, int $page): array
    {
        $total = $this->count();
        $page = max(1, min($page, (int) max(1, ceil($total / $perPage))));
        $rows = (clone $this)->limit($perPage)->offset(($page - 1) * $perPage)->get();

        return ['rows' => $rows, 'total' => $total, 'page' => $page];
    }

    public function paginate(int $perPage = 15, int $page = 1): Paginator
    {
        ['rows' => $rows, 'total' => $total, 'page' => $page] = $this->paginateRows($perPage, $page);

        return new Paginator($rows, $total, $perPage, $page);
    }

    /**
     * @return int The new row's id.
     */
    public function insert(array $values): int
    {
        $columns = implode(', ', array_map($this->db->quoteIdentifier(...), array_keys($values)));
        $placeholders = implode(', ', array_fill(0, count($values), '?'));

        $this->db->statement(
            'INSERT INTO ' . $this->db->quoteIdentifier($this->table) . " ($columns) VALUES ($placeholders)",
            array_values($values),
        );

        return $this->db->lastInsertId();
    }

    /**
     * @return int Number of affected rows.
     */
    public function update(array $values): int
    {
        $assignments = implode(', ', array_map(fn (string $column) => $this->db->quoteIdentifier($column) . ' = ?', array_keys($values)));

        return $this->db->statement(
            'UPDATE ' . $this->db->quoteIdentifier($this->table) . " SET $assignments" . $this->compileWheres(),
            [...array_values($values), ...$this->getBindings()],
        );
    }

    /**
     * @return int Number of deleted rows.
     */
    public function delete(): int
    {
        return $this->db->statement(
            'DELETE FROM ' . $this->db->quoteIdentifier($this->table) . $this->compileWheres(),
            $this->getBindings(),
        );
    }

    public function toSql(): string
    {
        $columns = implode(', ', array_map($this->db->quoteIdentifier(...), $this->columns));
        $sql = "SELECT $columns FROM " . $this->db->quoteIdentifier($this->table) . $this->compileWheres();

        if ($this->orders !== []) {
            $sql .= ' ORDER BY ' . implode(', ', $this->orders);
        }

        if ($this->limit !== null) {
            $sql .= ' LIMIT ' . $this->limit;
        }

        if ($this->offset !== null) {
            $sql .= ($this->limit === null ? ' LIMIT ' . PHP_INT_MAX : '') . ' OFFSET ' . $this->offset;
        }

        return $sql;
    }

    public function getBindings(): array
    {
        return array_merge(...array_column($this->wheres, 'bindings'));
    }

    private function addWhere(string $boolean, string $sql, array $bindings): self
    {
        $this->wheres[] = ['boolean' => strtoupper($boolean) === 'OR' ? 'OR' : 'AND', 'sql' => $sql, 'bindings' => $bindings];

        return $this;
    }

    private function compileWheres(): string
    {
        $sql = '';

        foreach ($this->wheres as $index => $where) {
            $sql .= ($index === 0 ? ' WHERE ' : " {$where['boolean']} ") . $where['sql'];
        }

        return $sql;
    }
}
