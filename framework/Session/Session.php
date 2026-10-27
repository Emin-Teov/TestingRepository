<?php

declare(strict_types=1);

namespace Framework\Session;

/**
 * Wrapper around $_SESSION with flash data and a per-session CSRF token.
 */
final class Session
{
    private const FLASH_KEY = '_flash';
    private const TOKEN_KEY = '_token';

    /** Flash data that was set during the previous request. */
    private array $flashed = [];

    private bool $started = false;

    public function __construct(private readonly array $options = [])
    {
    }

    public function start(bool $secure = false): void
    {
        if ($this->started) {
            return;
        }

        if (PHP_SAPI === 'cli') {
            $_SESSION ??= [];
        } elseif (session_status() !== PHP_SESSION_ACTIVE) {
            session_name($this->options['name'] ?? 'framework_session');
            session_set_cookie_params([
                'lifetime' => $this->options['lifetime'] ?? 0,
                'path' => '/',
                'secure' => $secure,
                'httponly' => true,
                'samesite' => $this->options['same_site'] ?? 'Lax',
            ]);
            session_start();
        }

        $this->flashed = $_SESSION[self::FLASH_KEY] ?? [];
        unset($_SESSION[self::FLASH_KEY]);
        $this->started = true;
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $_SESSION[$key] ?? $default;
    }

    public function put(string $key, mixed $value): void
    {
        $_SESSION[$key] = $value;
    }

    public function has(string $key): bool
    {
        return isset($_SESSION[$key]);
    }

    public function pull(string $key, mixed $default = null): mixed
    {
        $value = $this->get($key, $default);
        $this->forget($key);

        return $value;
    }

    public function forget(string $key): void
    {
        unset($_SESSION[$key]);
    }

    /**
     * Store a value that is available only during the next request.
     */
    public function flash(string $key, mixed $value): void
    {
        $_SESSION[self::FLASH_KEY][$key] = $value;
    }

    public function getFlash(string $key, mixed $default = null): mixed
    {
        return $this->flashed[$key] ?? $default;
    }

    public function old(string $key, mixed $default = ''): mixed
    {
        return $this->flashed['_old'][$key] ?? $default;
    }

    /**
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        return $this->flashed['_errors'] ?? [];
    }

    public function token(): string
    {
        return $_SESSION[self::TOKEN_KEY] ??= bin2hex(random_bytes(32));
    }

    public function regenerateToken(): void
    {
        $_SESSION[self::TOKEN_KEY] = bin2hex(random_bytes(32));
    }

    /**
     * Issue a new session id (prevents session fixation after login).
     */
    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function invalidate(): void
    {
        $_SESSION = [];
        $this->regenerate();
        $this->regenerateToken();
    }
}
