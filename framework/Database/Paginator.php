<?php

declare(strict_types=1);

namespace Framework\Database;

use JsonSerializable;

final readonly class Paginator implements JsonSerializable
{
    public function __construct(
        public array $items,
        public int $total,
        public int $perPage,
        public int $currentPage,
    ) {
    }

    public function lastPage(): int
    {
        return max(1, (int) ceil($this->total / max(1, $this->perPage)));
    }

    public function hasPages(): bool
    {
        return $this->lastPage() > 1;
    }

    public function isEmpty(): bool
    {
        return $this->items === [];
    }

    public function firstItem(): int
    {
        return $this->total === 0 ? 0 : ($this->currentPage - 1) * $this->perPage + 1;
    }

    public function lastItem(): int
    {
        return $this->total === 0 ? 0 : $this->firstItem() + count($this->items) - 1;
    }

    public function map(callable $callback): self
    {
        return new self(array_map($callback, $this->items), $this->total, $this->perPage, $this->currentPage);
    }

    public function jsonSerialize(): array
    {
        return [
            'data' => $this->items,
            'meta' => [
                'current_page' => $this->currentPage,
                'last_page' => $this->lastPage(),
                'per_page' => $this->perPage,
                'total' => $this->total,
            ],
        ];
    }
}
