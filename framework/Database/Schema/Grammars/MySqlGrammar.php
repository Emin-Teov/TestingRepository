<?php

declare(strict_types=1);

namespace Framework\Database\Schema\Grammars;

use Framework\Database\Schema\ColumnDefinition;

final class MySqlGrammar extends Grammar
{
    protected function quote(string $identifier): string
    {
        return '`' . str_replace('`', '``', $identifier) . '`';
    }

    protected function typeFor(ColumnDefinition $column): string
    {
        return match ($column->type) {
            'id' => 'BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY',
            'integer' => 'INT',
            'bigInteger' => 'BIGINT',
            'foreignId' => 'BIGINT UNSIGNED',
            'string' => 'VARCHAR(' . $column->options['length'] . ')',
            'text' => 'TEXT',
            'boolean' => 'TINYINT(1)',
            'decimal' => sprintf('DECIMAL(%d, %d)', $column->options['precision'], $column->options['scale']),
            'json' => 'JSON',
            'date' => 'DATE',
            'dateTime' => 'DATETIME',
        };
    }

    protected function tableSuffix(): string
    {
        return ' ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci';
    }

    public function compileDropAllTables(array $tables): array
    {
        return [
            'SET FOREIGN_KEY_CHECKS = 0',
            ...array_map(fn (string $table) => $this->compileDrop($table, true), $tables),
            'SET FOREIGN_KEY_CHECKS = 1',
        ];
    }

    public function compileTableExists(): string
    {
        return 'SELECT COUNT(*) AS aggregate FROM information_schema.tables WHERE table_schema = DATABASE() AND table_name = ?';
    }

    public function compileListTables(): string
    {
        return "SELECT table_name AS name FROM information_schema.tables WHERE table_schema = DATABASE() AND table_type = 'BASE TABLE'";
    }
}
