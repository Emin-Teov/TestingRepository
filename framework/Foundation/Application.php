<?php

declare(strict_types=1);

namespace Framework\Foundation;

use ErrorException;
use Framework\Container\Container;
use Framework\Support\Config;
use Framework\Support\Env;

final class Application extends Container
{
    public const VERSION = '2.0.0';

    public function __construct(private readonly string $basePath)
    {
        Container::setInstance($this);
        $this->instance(self::class, $this);
        $this->instance(Container::class, $this);
    }

    public function bootstrap(): self
    {
        Env::load($this->basePath('.env'));

        $config = new Config($this->configPath());
        $this->instance(Config::class, $config);

        date_default_timezone_set($config->get('app.timezone', 'UTC'));
        mb_internal_encoding('UTF-8');

        $this->registerErrorHandling((bool) $config->get('app.debug', false));

        return $this;
    }

    public function basePath(string $path = ''): string
    {
        return $path === '' ? $this->basePath : $this->basePath . DIRECTORY_SEPARATOR . ltrim($path, '\\/');
    }

    public function configPath(): string
    {
        return $this->basePath('config');
    }

    public function storagePath(string $path = ''): string
    {
        return $this->basePath('storage' . ($path !== '' ? "/$path" : ''));
    }

    public function databasePath(string $path = ''): string
    {
        return $this->basePath('database' . ($path !== '' ? "/$path" : ''));
    }

    public function resourcePath(string $path = ''): string
    {
        return $this->basePath('resources' . ($path !== '' ? "/$path" : ''));
    }

    public function langPath(): string
    {
        return $this->basePath('lang');
    }

    public function environment(): string
    {
        return (string) $this->make(Config::class)->get('app.env', 'production');
    }

    public function isProduction(): bool
    {
        return $this->environment() === 'production';
    }

    public function isDebug(): bool
    {
        return (bool) $this->make(Config::class)->get('app.debug', false);
    }

    public function runningInConsole(): bool
    {
        return PHP_SAPI === 'cli' || PHP_SAPI === 'phpdbg';
    }

    private function registerErrorHandling(bool $debug): void
    {
        error_reporting(E_ALL);
        ini_set('display_errors', $debug ? '1' : '0');

        // Turn warnings and notices into exceptions so they are never silently ignored.
        set_error_handler(static function (int $level, string $message, string $file, int $line): bool {
            if (!(error_reporting() & $level)) {
                return false;
            }

            throw new ErrorException($message, 0, $level, $file, $line);
        });
    }
}
