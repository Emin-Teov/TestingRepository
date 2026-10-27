<?php

declare(strict_types=1);

namespace Framework\Logging;

use Stringable;

final readonly class Logger
{
    public function __construct(private string $directory)
    {
    }

    public function error(string|Stringable $message, array $context = []): void
    {
        $this->write('ERROR', $message, $context);
    }

    public function warning(string|Stringable $message, array $context = []): void
    {
        $this->write('WARNING', $message, $context);
    }

    public function info(string|Stringable $message, array $context = []): void
    {
        $this->write('INFO', $message, $context);
    }

    private function write(string $level, string|Stringable $message, array $context): void
    {
        if (!is_dir($this->directory)) {
            @mkdir($this->directory, 0775, true);
        }

        $line = sprintf(
            "[%s] %s: %s%s\n",
            date('Y-m-d H:i:s'),
            $level,
            $message,
            $context !== [] ? ' ' . json_encode($context, JSON_UNESCAPED_UNICODE) : '',
        );

        @file_put_contents($this->directory . '/app-' . date('Y-m-d') . '.log', $line, FILE_APPEND | LOCK_EX);
    }
}
