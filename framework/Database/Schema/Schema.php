<?php

declare(strict_types=1);

namespace Framework\Database\Schema;

use Closure;
use Framework\Database\Connection;

/**
 * Database-agnostic DDL, used inside migrations:
 *
 *   $schema->create('items', function (Blueprint $table) {
 *       $table->id();
 *       $table->string('value', 55);
 *       $table->timestamps();
 *   });
 */
final readonly class Schema
{
    public function __construct(private Connection $db)
    {
    }

    /**
     * @param Closure(Blueprint): void $callback
     */
    public function create(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $this->run($this->db->grammar()->compileCreate($blueprint));
    }

    /**
     * Add or drop columns on an existing table.
     *
     * @param Closure(Blueprint): void $callback
     */
    public function table(string $table, Closure $callback): void
    {
        $blueprint = new Blueprint($table);
        $callback($blueprint);

        $this->run($this->db->grammar()->compileAlter($blueprint));
    }

    public function drop(string $table): void
    {
        $this->db->statement($this->db->grammar()->compileDrop($table, false));
    }

    public function dropIfExists(string $table): void
    {
        $this->db->statement($this->db->grammar()->compileDrop($table, true));
    }

    public function rename(string $from, string $to): void
    {
        $this->db->statement($this->db->grammar()->compileRename($from, $to));
    }

    public function hasTable(string $table): bool
    {
        return (int) ($this->db->selectOne($this->db->grammar()->compileTableExists(), [$table])['aggregate'] ?? 0) > 0;
    }

    /**
     * @return list<string>
     */
    public function tables(): array
    {
        return array_column($this->db->select($this->db->grammar()->compileListTables()), 'name');
    }

    public function dropAllTables(): void
    {
        $this->run($this->db->grammar()->compileDropAllTables($this->tables()));
    }

    private function run(array $statements): void
    {
        foreach ($statements as $sql) {
            $this->db->statement($sql);
        }
    }
}
