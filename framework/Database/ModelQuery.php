<?php

declare(strict_types=1);

namespace Framework\Database;

/**
 * A QueryBuilder that returns model instances instead of arrays.
 *
 * @template TModel of Model
 *
 * @method self where(string $column, mixed $operator, mixed $value = null, string $boolean = 'AND')
 * @method self orWhere(string $column, mixed $operator, mixed $value = null)
 * @method self whereIn(string $column, array $values)
 * @method self whereNull(string $column)
 * @method self whereNotNull(string $column)
 * @method self search(array $columns, string $term)
 * @method self orderBy(string $column, string $direction = 'asc')
 * @method self latest(string $column = 'id')
 * @method self limit(int $limit)
 * @method self offset(int $offset)
 * @method self select(string ...$columns)
 * @method int count()
 * @method float sum(string $column)
 * @method bool exists()
 * @method int delete()
 * @method int update(array $values)
 * @method string toSql()
 */
final class ModelQuery
{
    /**
     * @param class-string<TModel> $model
     */
    public function __construct(private QueryBuilder $query, private readonly string $model)
    {
    }

    /**
     * @return list<TModel>
     */
    public function get(): array
    {
        return array_map($this->model::hydrate(...), $this->query->get());
    }

    /**
     * @return TModel|null
     */
    public function first(): ?Model
    {
        $row = $this->query->first();

        return $row === null ? null : $this->model::hydrate($row);
    }

    /**
     * @return TModel|null
     */
    public function find(int $id): ?Model
    {
        return (clone $this)->where('id', $id)->first();
    }

    public function paginate(int $perPage = 15, int $page = 1): Paginator
    {
        return $this->query->paginate($perPage, $page)->map($this->model::hydrate(...));
    }

    public function __call(string $method, array $arguments): mixed
    {
        $result = $this->query->{$method}(...$arguments);

        return $result instanceof QueryBuilder ? $this : $result;
    }

    public function __clone()
    {
        // Clone the underlying builder too, so the copy can be modified independently.
        $this->query = clone $this->query;
    }
}
