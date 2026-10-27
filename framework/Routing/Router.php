<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;
use Framework\Exceptions\HttpException;
use Framework\Http\Request;
use InvalidArgumentException;

final class Router
{
    /** @var list<Route> */
    private array $routes = [];

    /** @var list<array{prefix: string, middleware: list<string>, as: string}> */
    private array $groupStack = [];

    public function get(string $uri, Closure|array $action): Route
    {
        return $this->addRoute(['GET'], $uri, $action);
    }

    public function post(string $uri, Closure|array $action): Route
    {
        return $this->addRoute(['POST'], $uri, $action);
    }

    public function put(string $uri, Closure|array $action): Route
    {
        return $this->addRoute(['PUT'], $uri, $action);
    }

    public function patch(string $uri, Closure|array $action): Route
    {
        return $this->addRoute(['PATCH'], $uri, $action);
    }

    public function delete(string $uri, Closure|array $action): Route
    {
        return $this->addRoute(['DELETE'], $uri, $action);
    }

    /**
     * Register the standard CRUD routes for a controller.
     *
     * GET /items, GET /items/create, POST /items, GET /items/{id}/edit, PUT /items/{id}, DELETE /items/{id}
     */
    public function resource(string $uri, string $controller, ?string $name = null): void
    {
        $uri = '/' . trim($uri, '/');
        $name ??= trim(str_replace('/', '.', $uri), '.');

        $this->get($uri, [$controller, 'index'])->name("$name.index");
        $this->get("$uri/create", [$controller, 'create'])->name("$name.create");
        $this->post($uri, [$controller, 'store'])->name("$name.store");
        $this->get("$uri/{id:\d+}/edit", [$controller, 'edit'])->name("$name.edit");
        $this->put("$uri/{id:\d+}", [$controller, 'update'])->name("$name.update");
        $this->delete("$uri/{id:\d+}", [$controller, 'destroy'])->name("$name.destroy");
    }

    /**
     * @param array{prefix?: string, middleware?: list<string>|string, as?: string} $attributes
     * @param Closure(Router): void $routes
     */
    public function group(array $attributes, Closure $routes): void
    {
        $parent = end($this->groupStack) ?: ['prefix' => '', 'middleware' => [], 'as' => ''];

        $this->groupStack[] = [
            'prefix' => rtrim($parent['prefix'] . '/' . trim($attributes['prefix'] ?? '', '/'), '/'),
            'middleware' => [...$parent['middleware'], ...(array) ($attributes['middleware'] ?? [])],
            'as' => $parent['as'] . ($attributes['as'] ?? ''),
        ];

        $routes($this);

        array_pop($this->groupStack);
    }

    /**
     * @return array{Route, array<string, string>}
     */
    public function match(Request $request): array
    {
        $allowed = [];

        foreach ($this->routes as $route) {
            $parameters = $route->matches($request->path);

            if ($parameters === null) {
                continue;
            }

            if ($route->allowsMethod($request->method)) {
                return [$route, $parameters];
            }

            array_push($allowed, ...$route->methods());
        }

        if ($allowed !== []) {
            throw new HttpException(405, '', ['Allow' => implode(', ', array_unique($allowed))]);
        }

        throw new HttpException(404);
    }

    public function path(string $name, array $parameters = []): string
    {
        foreach ($this->routes as $route) {
            if ($route->getName() === $name) {
                return $route->path($parameters);
            }
        }

        throw new InvalidArgumentException("Route [$name] is not defined.");
    }

    /**
     * @return list<Route>
     */
    public function routes(): array
    {
        return $this->routes;
    }

    private function addRoute(array $methods, string $uri, Closure|array $action): Route
    {
        $group = end($this->groupStack) ?: ['prefix' => '', 'middleware' => [], 'as' => ''];
        $uri = '/' . trim($group['prefix'] . '/' . trim($uri, '/'), '/');

        return $this->routes[] = new Route($methods, $uri, $action, $group['middleware'], $group['as']);
    }
}
