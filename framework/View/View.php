<?php

declare(strict_types=1);

namespace Framework\View;

use InvalidArgumentException;

/**
 * Locates and renders plain-PHP templates from resources/views.
 */
final class View
{
    private array $shared = [];

    public function __construct(private readonly string $path)
    {
    }

    /**
     * Make a value available to every template.
     */
    public function share(string $key, mixed $value): void
    {
        $this->shared[$key] = $value;
    }

    public function render(string $name, array $data = []): string
    {
        return (new Template($this))->render($name, [...$this->shared, ...$data]);
    }

    /**
     * Resolve "admin.items.index" or "admin/items/index" to a file path.
     */
    public function find(string $name): string
    {
        $relative = str_replace('.', '/', $name);

        if (str_contains($relative, '..') || !preg_match('#^[\w/-]+$#', $relative)) {
            throw new InvalidArgumentException("Invalid view name [$name].");
        }

        $file = $this->path . '/' . $relative . '.php';

        if (!is_file($file)) {
            throw new InvalidArgumentException("View [$name] not found.");
        }

        return $file;
    }
}
