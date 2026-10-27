<?php

declare(strict_types=1);

namespace Framework\Database\Schema\Grammars;

use Framework\Database\Schema\ColumnDefinition;

final class SqliteGrammar extends Grammar
{
    protected function quote(string $identifier): string
    {
        return '"' . str_replace('"', '""', $identifier) . '"';
    }

    protected function typeFor(ColumnDefinition $column): string
    {
        return match ($column->type) {
            'id' => 'INTEGER PRIMARY KEY AUTOINCREMENT',
            'integer', 'bigInteger', 'foreignId', 'boolean' => 'INTEGER',
            'string' => 'VARCHAR(' . $column->options['length'] . ')',
            'text', 'json' => 'TEXT',
            'decimal' => 'NUMERIC',
            'date' => 'DATE',
            'dateTime' => 'DATETIME',
        };
    }

    public function compileDropAllTables(array $tables): array
    {
        return [
            'PRAGMA foreign_keys = OFF',
            ...array_map(fn (string $table) => $this->compileDrop($table, true), $tables),
            'PRAGMA foreign_keys = ON',
        ];
    }

    public function compileTableExists(): string
    {
        return "SELECT COUNT(*) AS aggregate FROM sqlite_master WHERE type = 'table' AND name = ?";
    }

    public function compileListTables(): string
    {
        return "SELECT name FROM sqlite_master WHERE type = 'table' AND name NOT LIKE 'sqlite_%'";
    }
}
