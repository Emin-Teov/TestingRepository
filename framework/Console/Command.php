<?php

declare(strict_types=1);

namespace Framework\Console;

use Framework\Foundation\Application;

abstract class Command
{
    public const SUCCESS = 0;
    public const FAILURE = 1;

    /** @var list<string> */
    private array $arguments = [];

    /** @var array<string, string|true> */
    private array $options = [];

    public function __construct(protected readonly Application $app)
    {
    }

    abstract public function handle(): int;

    /**
     * Split argv into positional arguments and "--name=value" / "--flag" options.
     */
    public function setInput(array $input): void
    {
        foreach ($input as $token) {
            if (str_starts_with($token, '--')) {
                [$name, $value] = array_pad(explode('=', substr($token, 2), 2), 2, true);
                $this->options[$name] = $value;
            } else {
                $this->arguments[] = $token;
            }
        }
    }

    protected function argument(int $position, ?string $default = null): ?string
    {
        return $this->arguments[$position] ?? $default;
    }

    protected function option(string $name, string|bool|null $default = null): string|bool|null
    {
        return $this->options[$name] ?? $default;
    }

    /**
     * Destructive commands refuse to run in production unless --force is given.
     */
    protected function confirmProduction(): bool
    {
        if (!$this->app->isProduction() || $this->option('force')) {
            return true;
        }

        $this->error('The application is in production. Use --force to run this command anyway.');

        return false;
    }

    protected function line(string $message = ''): void
    {
        fwrite(STDOUT, $message . PHP_EOL);
    }

    protected function info(string $message): void
    {
        $this->line("\033[32m$message\033[0m");
    }

    protected function comment(string $message): void
    {
        $this->line("\033[33m$message\033[0m");
    }

    protected function error(string $message): void
    {
        fwrite(STDERR, "\033[31m$message\033[0m" . PHP_EOL);
    }

    /**
     * @param list<string> $headers
     * @param list<list<scalar|null>> $rows
     */
    protected function table(array $headers, array $rows): void
    {
        $widths = array_map(mb_strlen(...), $headers);

        foreach ($rows as $row) {
            foreach (array_values($row) as $i => $cell) {
                $widths[$i] = max($widths[$i] ?? 0, mb_strlen((string) $cell));
            }
        }

        $border = '+' . implode('+', array_map(static fn (int $w) => str_repeat('-', $w + 2), $widths)) . '+';
        $format = static fn (array $cells) => '| ' . implode(' | ', array_map(
            static fn ($cell, int $i) => str_pad((string) $cell, $widths[$i] + strlen((string) $cell) - mb_strlen((string) $cell)),
            array_values($cells),
            array_keys(array_values($cells)),
        )) . ' |';

        $this->line($border);
        $this->line($format($headers));
        $this->line($border);
        array_map(fn (array $row) => $this->line($format($row)), $rows);
        $this->line($border);
    }
}
