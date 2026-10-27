<?php

declare(strict_types=1);

namespace Tests\Unit;

use Framework\Exceptions\HttpException;
use Framework\Http\Request;
use Framework\Routing\Router;
use PHPUnit\Framework\TestCase;

final class RouterTest extends TestCase
{
    public function test_it_matches_parameters_with_patterns(): void
    {
        $router = new Router();
        $router->get('/items/{id:\d+}', fn () => null)->name('items.show');
        $router->post('/locale/{locale:[a-z]{2}}', fn () => null);

        [$route, $parameters] = $router->match(Request::create('GET', '/items/42'));
        $this->assertSame('items.show', $route->getName());
        $this->assertSame(['id' => '42'], $parameters);

        [, $parameters] = $router->match(Request::create('POST', '/locale/az'));
        $this->assertSame(['locale' => 'az'], $parameters);
    }

    public function test_groups_combine_prefix_names_and_middleware(): void
    {
        $router = new Router();
        $router->group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => 'auth'], function (Router $router): void {
            $router->group(['middleware' => 'role:admin'], function (Router $router): void {
                $router->resource('users', 'UserController');
            });
        });

        [$route] = $router->match(Request::create('PUT', '/admin/users/3'));

        $this->assertSame('admin.users.update', $route->getName());
        $this->assertSame(['auth', 'role:admin'], $route->getMiddleware());
        $this->assertSame('/admin/users/3/edit', $router->path('admin.users.edit', ['id' => 3]));
    }

    public function test_unknown_path_is_404(): void
    {
        $router = new Router();
        $router->get('/items/{id:\d+}', fn () => null);

        $this->expectExceptionObject(new HttpException(404));
        $router->match(Request::create('GET', '/items/abc'));
    }

    public function test_wrong_method_is_405_with_allow_header(): void
    {
        $router = new Router();
        $router->get('/login', fn () => null);
        $router->post('/login', fn () => null);

        try {
            $router->match(Request::create('DELETE', '/login'));
            $this->fail('Expected a 405.');
        } catch (HttpException $exception) {
            $this->assertSame(405, $exception->status());
            $this->assertSame(['Allow' => 'GET, POST'], $exception->headers());
        }
    }
}
