<?php

declare(strict_types=1);

namespace Framework\Database\Schema\Grammars;

use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\ColumnDefinition;

/**
 * Turns a Blueprint into SQL for one database driver.
 */
abstract class Grammar
{
    abstract protected function quote(string $identifier): string;

    abstract protected function typeFor(ColumnDefinition $column): string;

    /**
     * @return list<string>
     */
    abstract public function compileDropAllTables(array $tables): array;

    abstract public function compileTableExists(): string;

    abstract public function compileListTables(): string;

    /**
     * @return list<string>
     */
    public function compileCreate(Blueprint $blueprint): array
    {
        $lines = array_map($this->compileColumn(...), $blueprint->columns());

        foreach ($blueprint->columns() as $column) {
            if ($column->unique) {
                $lines[] = sprintf('CONSTRAINT %s UNIQUE (%s)', $this->quote("{$blueprint->table}_{$column->name}_unique"), $this->quote($column->name));
            }

            if ($column->references !== null) {
                $lines[] = $this->compileForeignKey($column);
            }
        }

        $sql = sprintf("CREATE TABLE %s (\n    %s\n)%s", $this->quote($blueprint->table), implode(",\n    ", $lines), $this->tableSuffix());

        return [$sql, ...$this->compileIndexes($blueprint)];
    }

    /**
     * @return list<string>
     */
    public function compileAlter(Blueprint $blueprint): array
    {
        $statements = [];
        $table = $this->quote($blueprint->table);

        foreach ($blueprint->columns() as $column) {
            $statements[] = "ALTER TABLE $table ADD COLUMN " . $this->compileColumn($column);

            if ($column->unique) {
                $statements[] = sprintf('CREATE UNIQUE INDEX %s ON %s (%s)', $this->quote("{$blueprint->table}_{$column->name}_unique"), $table, $this->quote($column->name));
            }
        }

        foreach ($blueprint->droppedColumns() as $name) {
            $statements[] = "ALTER TABLE $table DROP COLUMN " . $this->quote($name);
        }

        return [...$statements, ...$this->compileIndexes($blueprint)];
    }

    public function compileDrop(string $table, bool $ifExists): string
    {
        return 'DROP TABLE ' . ($ifExists ? 'IF EXISTS ' : '') . $this->quote($table);
    }

    public function compileRename(string $from, string $to): string
    {
        return sprintf('ALTER TABLE %s RENAME TO %s', $this->quote($from), $this->quote($to));
    }

    protected function tableSuffix(): string
    {
        return '';
    }

    protected function compileColumn(ColumnDefinition $column): string
    {
        $sql = $this->quote($column->name) . ' ' . $this->typeFor($column);

        if ($column->type === 'id') {
            return $sql;
        }

        $sql .= $column->nullable ? ' NULL' : ' NOT NULL';

        if ($column->hasDefault) {
            $sql .= ' DEFAULT ' . $this->literal($column->default);
        }

        return $sql;
    }

    protected function compileForeignKey(ColumnDefinition $column): string
    {
        $sql = sprintf(
            'FOREIGN KEY (%s) REFERENCES %s (%s)',
            $this->quote($column->name),
            $this->quote((string) $column->references),
            $this->quote($column->referencedColumn),
        );

        return $column->onDelete !== null ? "$sql ON DELETE {$column->onDelete}" : $sql;
    }

    /**
     * @return list<string>
     */
    protected function compileIndexes(Blueprint $blueprint): array
    {
        $statements = [];

        foreach ($blueprint->columns() as $column) {
            if ($column->index) {
                $statements[] = sprintf(
                    'CREATE INDEX %s ON %s (%s)',
                    $this->quote("{$blueprint->table}_{$column->name}_index"),
                    $this->quote($blueprint->table),
                    $this->quote($column->name),
                );
            }
        }

        return $statements;
    }

    protected function literal(mixed $value): string
    {
        return match (true) {
            $value === null => 'NULL',
            is_bool($value) => $value ? '1' : '0',
            is_int($value), is_float($value) => (string) $value,
            default => "'" . str_replace("'", "''", (string) $value) . "'",
        };
    }
}
