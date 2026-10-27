<?php

declare(strict_types=1);

namespace Framework\Auth;

/**
 * File-based attempt counter, used to slow down password guessing.
 */
final readonly class RateLimiter
{
    private string $directory;

    public function __construct(?string $directory = null)
    {
        $this->directory = $directory ?? storage_path('cache/rate-limits');
    }

    public function tooManyAttempts(string $key, int $maxAttempts): bool
    {
        return $this->read($key)['attempts'] >= $maxAttempts;
    }

    public function hit(string $key, int $decaySeconds): void
    {
        $record = $this->read($key);

        $this->write($key, [
            'attempts' => $record['attempts'] + 1,
            'reset_at' => $record['attempts'] === 0 ? time() + $decaySeconds : $record['reset_at'],
        ]);
    }

    public function availableIn(string $key): int
    {
        return max(0, $this->read($key)['reset_at'] - time());
    }

    public function clear(string $key): void
    {
        $file = $this->file($key);

        if (is_file($file)) {
            unlink($file);
        }
    }

    /**
     * @return array{attempts: int, reset_at: int}
     */
    private function read(string $key): array
    {
        $file = $this->file($key);
        $record = is_file($file) ? json_decode((string) file_get_contents($file), true) : null;

        if (!is_array($record) || $record['reset_at'] <= time()) {
            return ['attempts' => 0, 'reset_at' => 0];
        }

        return $record;
    }

    private function write(string $key, array $record): void
    {
        if (!is_dir($this->directory)) {
            mkdir($this->directory, 0775, true);
        }

        file_put_contents($this->file($key), json_encode($record), LOCK_EX);
    }

    private function file(string $key): string
    {
        return $this->directory . '/' . sha1($key) . '.json';
    }
}
