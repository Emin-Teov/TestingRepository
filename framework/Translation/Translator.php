<?php

declare(strict_types=1);

namespace Framework\Translation;

use Framework\Support\Arr;

/**
 * Loads lang/{locale}.php arrays and resolves keys such as "items.title".
 */
final class Translator
{
    /** @var array<string, array> */
    private array $loaded = [];

    public function __construct(
        private readonly string $path,
        private string $locale,
        private readonly string $fallback = 'en',
    ) {
    }

    public function getLocale(): string
    {
        return $this->locale;
    }

    public function setLocale(string $locale): void
    {
        $this->locale = $locale;
    }

    public function has(string $key, ?string $locale = null): bool
    {
        return is_string(Arr::get($this->load($locale ?? $this->locale), $key));
    }

    /**
     * @param array<string, scalar> $replace Values for ":placeholder" markers.
     */
    public function get(string $key, array $replace = [], ?string $locale = null): string
    {
        $line = Arr::get($this->load($locale ?? $this->locale), $key)
            ?? Arr::get($this->load($this->fallback), $key);

        if (!is_string($line)) {
            return $key;
        }

        // Longest keys first, so ":min" does not clobber ":minimum".
        uksort($replace, static fn (string $a, string $b) => strlen($b) <=> strlen($a));

        foreach ($replace as $name => $value) {
            $line = str_replace(':' . $name, (string) $value, $line);
        }

        return $line;
    }

    private function load(string $locale): array
    {
        if (!isset($this->loaded[$locale])) {
            $file = $this->path . '/' . basename($locale) . '.php';
            $this->loaded[$locale] = is_file($file) ? require $file : [];
        }

        return $this->loaded[$locale];
    }
}
