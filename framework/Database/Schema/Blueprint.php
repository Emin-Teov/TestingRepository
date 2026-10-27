<?php

declare(strict_types=1);

namespace Framework\Database\Schema;

/**
 * Describes the columns of a table being created or altered inside a migration.
 */
final class Blueprint
{
    /** @var list<ColumnDefinition> */
    private array $columns = [];

    /** @var list<string> */
    private array $droppedColumns = [];

    public function __construct(public readonly string $table)
    {
    }

    /**
     * Auto-incrementing primary key.
     */
    public function id(string $name = 'id'): ColumnDefinition
    {
        return $this->addColumn('id', $name);
    }

    public function integer(string $name): ColumnDefinition
    {
        return $this->addColumn('integer', $name);
    }

    public function bigInteger(string $name): ColumnDefinition
    {
        return $this->addColumn('bigInteger', $name);
    }

    /**
     * Unsigned big integer for referencing another table's id, usually followed by ->constrained().
     */
    public function foreignId(string $name): ColumnDefinition
    {
        return $this->addColumn('foreignId', $name);
    }

    public function string(string $name, int $length = 255): ColumnDefinition
    {
        return $this->addColumn('string', $name, ['length' => $length]);
    }

    public function text(string $name): ColumnDefinition
    {
        return $this->addColumn('text', $name);
    }

    public function boolean(string $name): ColumnDefinition
    {
        return $this->addColumn('boolean', $name);
    }

    public function decimal(string $name, int $precision = 10, int $scale = 2): ColumnDefinition
    {
        return $this->addColumn('decimal', $name, ['precision' => $precision, 'scale' => $scale]);
    }

    public function json(string $name): ColumnDefinition
    {
        return $this->addColumn('json', $name);
    }

    public function date(string $name): ColumnDefinition
    {
        return $this->addColumn('date', $name);
    }

    public function dateTime(string $name): ColumnDefinition
    {
        return $this->addColumn('dateTime', $name);
    }

    /**
     * Nullable created_at and updated_at columns, maintained automatically by Model::save().
     */
    public function timestamps(): void
    {
        $this->dateTime('created_at')->nullable();
        $this->dateTime('updated_at')->nullable();
    }

    public function dropColumn(string ...$names): void
    {
        array_push($this->droppedColumns, ...$names);
    }

    /**
     * @return list<ColumnDefinition>
     */
    public function columns(): array
    {
        return $this->columns;
    }

    /**
     * @return list<string>
     */
    public function droppedColumns(): array
    {
        return $this->droppedColumns;
    }

    private function addColumn(string $type, string $name, array $options = []): ColumnDefinition
    {
        return $this->columns[] = new ColumnDefinition($type, $name, $options);
    }
}
