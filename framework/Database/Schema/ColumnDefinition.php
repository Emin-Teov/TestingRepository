<?php

declare(strict_types=1);

namespace Framework\Database\Schema;

/**
 * A column in a Blueprint, configured fluently: $table->string('email')->nullable()->unique().
 */
final class ColumnDefinition
{
    public bool $nullable = false;

    public bool $hasDefault = false;

    public mixed $default = null;

    public bool $unique = false;

    public bool $index = false;

    public ?string $references = null;

    public string $referencedColumn = 'id';

    public ?string $onDelete = null;

    public function __construct(
        public readonly string $type,
        public readonly string $name,
        public readonly array $options = [],
    ) {
    }

    public function nullable(bool $nullable = true): self
    {
        $this->nullable = $nullable;

        return $this;
    }

    public function default(mixed $value): self
    {
        $this->hasDefault = true;
        $this->default = $value instanceof \BackedEnum ? $value->value : $value;

        return $this;
    }

    public function unique(): self
    {
        $this->unique = true;

        return $this;
    }

    public function index(): self
    {
        $this->index = true;

        return $this;
    }

    /**
     * Add a foreign key to the given table (defaults to the column name without "_id", pluralised).
     */
    public function constrained(?string $table = null, string $column = 'id'): self
    {
        $this->references = $table ?? preg_replace('/_id$/', '', $this->name) . 's';
        $this->referencedColumn = $column;

        return $this;
    }

    public function cascadeOnDelete(): self
    {
        $this->onDelete = 'CASCADE';

        return $this;
    }

    public function nullOnDelete(): self
    {
        $this->onDelete = 'SET NULL';

        return $this;
    }
}
