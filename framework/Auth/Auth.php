<?php

declare(strict_types=1);

namespace Framework\Auth;

use Framework\Database\Model;
use Framework\Session\Session;

/**
 * Session-based authentication against the model configured in config/auth.php.
 */
final class Auth
{
    private const SESSION_KEY = 'auth_id';

    private ?Model $user = null;

    private bool $resolved = false;

    /**
     * @param array{model: class-string<Model>, username: string} $config
     */
    public function __construct(private readonly Session $session, private readonly array $config)
    {
    }

    public function attempt(string $username, string $password): bool
    {
        $model = $this->config['model'];
        $user = $model::query()->where($this->config['username'], $username)->first();

        if ($user === null || !Hash::check($password, (string) $user->password)) {
            return false;
        }

        if (Hash::needsRehash((string) $user->password)) {
            $user->update(['password' => Hash::make($password)]);
        }

        $this->login($user);

        return true;
    }

    public function login(Model $user): void
    {
        $this->session->regenerate();
        $this->session->regenerateToken();
        $this->session->put(self::SESSION_KEY, $user->getKey());

        $this->user = $user;
        $this->resolved = true;
    }

    public function logout(): void
    {
        $this->session->invalidate();
        $this->user = null;
        $this->resolved = true;
    }

    public function user(): ?Model
    {
        if (!$this->resolved) {
            $id = $this->session->get(self::SESSION_KEY);
            $model = $this->config['model'];
            $this->user = $id !== null ? $model::find((int) $id) : null;
            $this->resolved = true;
        }

        return $this->user;
    }

    public function id(): ?int
    {
        return $this->user()?->getKey();
    }

    public function check(): bool
    {
        return $this->user() !== null;
    }

    public function guest(): bool
    {
        return !$this->check();
    }
}
