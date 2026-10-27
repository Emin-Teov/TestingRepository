<?php

declare(strict_types=1);

namespace Tests;

use App\Enums\Permission;
use App\Models\User;
use App\Models\UserType;
use Framework\Auth\Auth;
use Framework\Auth\Hash;
use Framework\Auth\RateLimiter;
use Framework\Database\Migrations\Migrator;
use Framework\Foundation\Application;
use Framework\Http\Kernel;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Session\Session;
use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Boots a fresh application on an in-memory SQLite database for every test.
 */
abstract class TestCase extends BaseTestCase
{
    protected Application $app;

    protected function setUp(): void
    {
        parent::setUp();

        $_SESSION = [];
        $this->app = require dirname(__DIR__) . '/bootstrap/app.php';
        $this->app->make(Migrator::class)->run();

        // Keep login throttling files out of the real storage directory.
        $this->app->instance(RateLimiter::class, new RateLimiter(sys_get_temp_dir() . '/framework-tests-' . getmypid()));
    }

    protected function tearDown(): void
    {
        restore_error_handler();
        parent::tearDown();
    }

    /**
     * Send a request through the HTTP kernel. Each call uses a fresh session object over the
     * same $_SESSION array, just like consecutive browser requests.
     */
    protected function call(string $method, string $path, array $data = [], array $server = []): Response
    {
        $session = new Session();
        $this->app->instance(Session::class, $session);
        $this->app->singleton(Auth::class, fn () => new Auth($session, config('auth')));

        if (!in_array(strtoupper($method), ['GET', 'HEAD'], true) && !array_key_exists('_token', $data)) {
            $data['_token'] = $this->csrfToken();
        }

        return $this->app->make(Kernel::class)->handle(Request::create($method, $path, $data, $server));
    }

    protected function csrfToken(): string
    {
        return $_SESSION['_token'] ??= bin2hex(random_bytes(32));
    }

    /**
     * A user whose type grants exactly the given permissions; with none given, an Administrator.
     */
    protected function createUser(array $permissions = [], string $username = 'admin', string $password = 'password123', bool $active = true): User
    {
        $type = (new UserType())->forceFill([
            'name' => 'Type of ' . $username,
            'is_locked' => $permissions === [],
            ...array_map(static fn () => false, array_flip(Permission::columns())),
            ...array_fill_keys(array_map(static fn (Permission $permission) => $permission->column(), $permissions), true),
        ]);
        $type->save();

        return User::create(['usertype_id' => $type->id, 'username' => $username, 'password' => Hash::make($password), 'is_active' => $active]);
    }

    protected function actingAs(User $user): void
    {
        $_SESSION['auth_id'] = $user->id;
    }
}
