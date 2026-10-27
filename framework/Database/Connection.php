<?php

declare(strict_types=1);

namespace Framework\Database;

use Closure;
use Framework\Database\Schema\Grammars\Grammar;
use Framework\Database\Schema\Grammars\MySqlGrammar;
use Framework\Database\Schema\Grammars\SqliteGrammar;
use Framework\Database\Schema\Schema;
use InvalidArgumentException;
use PDO;
use Throwable;

/**
 * Lazily-opened PDO connection. Every query uses prepared statements.
 */
final class Connection
{
    private ?PDO $pdo = null;

    private ?Grammar $grammar = null;

    private bool $pretending = false;

    /** @var list<string> */
    private array $pretendedQueries = [];

    public function __construct(private readonly array $config, private readonly string $basePath = '')
    {
        if (!in_array($config['driver'] ?? null, ['mysql', 'sqlite'], true)) {
            throw new InvalidArgumentException('Unsupported database driver: ' . ($config['driver'] ?? 'none'));
        }
    }

    public function driver(): string
    {
        return $this->config['driver'];
    }

    public function databaseName(): string
    {
        return (string) $this->config['database'];
    }

    public function pdo(): PDO
    {
        return $this->pdo ??= $this->connect();
    }

    public function grammar(): Grammar
    {
        return $this->grammar ??= $this->driver() === 'mysql' ? new MySqlGrammar() : new SqliteGrammar();
    }

    public function table(string $table): QueryBuilder
    {
        return new QueryBuilder($this, $table);
    }

    public function schema(): Schema
    {
        return new Schema($this);
    }

    /**
     * @return list<array<string, mixed>>
     */
    public function select(string $sql, array $bindings = []): array
    {
        $statement = $this->pdo()->prepare($sql);
        $statement->execute(array_values($bindings));

        return $statement->fetchAll();
    }

    public function selectOne(string $sql, array $bindings = []): ?array
    {
        return $this->select($sql, $bindings)[0] ?? null;
    }

    /**
     * Run an INSERT/UPDATE/DELETE/DDL statement and return the number of affected rows.
     */
    public function statement(string $sql, array $bindings = []): int
    {
        if ($this->pretending) {
            $this->pretendedQueries[] = $this->interpolate($sql, $bindings);

            return 0;
        }

        $statement = $this->pdo()->prepare($sql);
        $statement->execute(array_values($bindings));

        return $statement->rowCount();
    }

    public function lastInsertId(): int
    {
        return (int) $this->pdo()->lastInsertId();
    }

    /**
     * @template T
     * @param Closure(Connection): T $callback
     * @return T
     */
    public function transaction(Closure $callback): mixed
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            $result = $callback($this);
            $pdo->commit();

            return $result;
        } catch (Throwable $exception) {
            if ($pdo->inTransaction()) {
                $pdo->rollBack();
            }

            throw $exception;
        }
    }

    /**
     * Collect the SQL a callback would run, without executing it.
     *
     * @return list<string>
     */
    public function pretend(Closure $callback): array
    {
        $this->pretending = true;
        $this->pretendedQueries = [];

        try {
            $callback($this);

            return $this->pretendedQueries;
        } finally {
            $this->pretending = false;
        }
    }

    /**
     * Quote a table or column name. Only plain identifiers ("users", "users.id") are accepted,
     * so user input can never be interpreted as SQL.
     */
    public function quoteIdentifier(string $name): string
    {
        if ($name === '*') {
            return '*';
        }

        if (!preg_match('/^[A-Za-z_][A-Za-z0-9_]*(\.([A-Za-z_][A-Za-z0-9_]*|\*))?$/', $name)) {
            throw new InvalidArgumentException("Invalid identifier [$name].");
        }

        $quote = $this->driver() === 'mysql' ? '`' : '"';

        return implode('.', array_map(
            static fn (string $part) => $part === '*' ? '*' : $quote . $part . $quote,
            explode('.', $name),
        ));
    }

    private function connect(): PDO
    {
        $options = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
        ];

        if ($this->driver() === 'sqlite') {
            $pdo = new PDO('sqlite:' . $this->sqlitePath(), null, null, $options);
            $pdo->exec('PRAGMA foreign_keys = ON');

            return $pdo;
        }

        $dsn = sprintf(
            'mysql:host=%s;port=%s;dbname=%s;charset=%s',
            $this->config['host'],
            $this->config['port'] ?? 3306,
            $this->config['database'],
            $this->config['charset'] ?? 'utf8mb4',
        );

        return new PDO($dsn, $this->config['username'], $this->config['password'], $options);
    }

    private function sqlitePath(): string
    {
        $path = $this->config['database'];

        if ($path === ':memory:') {
            return $path;
        }

        $isAbsolute = preg_match('#^([A-Za-z]:)?[\\\\/]#', $path) === 1;
        $path = $isAbsolute ? $path : rtrim($this->basePath, '\\/') . '/' . $path;

        if (!is_dir(dirname($path))) {
            mkdir(dirname($path), 0775, true);
        }

        return $path;
    }

    private function interpolate(string $sql, array $bindings): string
    {
        foreach ($bindings as $value) {
            $literal = match (true) {
                $value === null => 'NULL',
                is_bool($value) => $value ? '1' : '0',
                is_int($value), is_float($value) => (string) $value,
                default => "'" . str_replace("'", "''", (string) $value) . "'",
            };
            $sql = preg_replace('/\?/', str_replace(['\\', '$'], ['\\\\', '\\$'], $literal), $sql, 1);
        }

        return $sql;
    }
}
