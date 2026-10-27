<?php

declare(strict_types=1);

namespace Framework\Support;

final class Config
{
    private array $items = [];

    public function __construct(string $path)
    {
        foreach (glob($path . '/*.php') ?: [] as $file) {
            $this->items[basename($file, '.php')] = require $file;
        }
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->items, $key, $default);
    }

    public function set(string $key, mixed $value): void
    {
        Arr::set($this->items, $key, $value);
    }
}
