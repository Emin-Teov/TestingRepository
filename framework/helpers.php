<?php

declare(strict_types=1);

use Framework\Auth\Auth;
use Framework\Container\Container;
use Framework\Exceptions\HttpException;
use Framework\Foundation\Application;
use Framework\Http\JsonResponse;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Routing\Router;
use Framework\Session\Session;
use Framework\Support\Config;
use Framework\Support\Env;
use Framework\Translation\Translator;
use Framework\View\View;

if (!function_exists('app')) {
    /**
     * @template T of object
     * @param class-string<T>|null $id
     * @return ($id is null ? Application : T)
     */
    function app(?string $id = null): mixed
    {
        $container = Container::getInstance();

        return $id === null ? $container : $container->make($id);
    }
}

if (!function_exists('env')) {
    function env(string $key, mixed $default = null): mixed
    {
        return Env::get($key, $default);
    }
}

if (!function_exists('config')) {
    function config(string $key, mixed $default = null): mixed
    {
        return app(Config::class)->get($key, $default);
    }
}

if (!function_exists('base_path')) {
    function base_path(string $path = ''): string
    {
        return app()->basePath($path);
    }
}

if (!function_exists('storage_path')) {
    function storage_path(string $path = ''): string
    {
        return app()->storagePath($path);
    }
}

if (!function_exists('e')) {
    /**
     * Escape a value for safe output in HTML.
     */
    function e(mixed $value): string
    {
        if ($value instanceof BackedEnum) {
            $value = $value->value;
        }

        return htmlspecialchars((string) $value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}

if (!function_exists('__')) {
    function __(string $key, array $replace = []): string
    {
        return app(Translator::class)->get($key, $replace);
    }
}

if (!function_exists('view')) {
    function view(string $name, array $data = [], int $status = 200): Response
    {
        return new Response(app(View::class)->render($name, $data), $status, ['Content-Type' => 'text/html; charset=utf-8']);
    }
}

if (!function_exists('json')) {
    function json(mixed $data, int $status = 200): JsonResponse
    {
        return new JsonResponse($data, $status);
    }
}

if (!function_exists('request')) {
    function request(): Request
    {
        return app(Request::class);
    }
}

if (!function_exists('session')) {
    function session(): Session
    {
        return app(Session::class);
    }
}

if (!function_exists('auth')) {
    function auth(): Auth
    {
        return app(Auth::class);
    }
}

if (!function_exists('url')) {
    /**
     * Build a URL that respects the folder the app is installed in (e.g. http://localhost/Framework).
     */
    function url(string $path = '', array $query = []): string
    {
        $base = (string) config('app.url');

        if ($base === '' && app()->has(Request::class)) {
            $base = request()->basePath;
        }

        $url = rtrim($base, '/') . '/' . ltrim($path, '/');

        return $query !== [] ? $url . '?' . http_build_query($query) : $url;
    }
}

if (!function_exists('route')) {
    function route(string $name, array $parameters = [], array $query = []): string
    {
        return url(app(Router::class)->path($name, $parameters), $query);
    }
}

if (!function_exists('asset')) {
    /**
     * URL of a file in public/, with a cache-busting version based on its modification time.
     */
    function asset(string $path): string
    {
        $file = base_path('public/' . ltrim($path, '/'));

        return url($path) . (is_file($file) ? '?v=' . filemtime($file) : '');
    }
}

if (!function_exists('redirect')) {
    function redirect(string $to): RedirectResponse
    {
        return new RedirectResponse(preg_match('#^https?://#', $to) ? $to : url($to));
    }
}

if (!function_exists('to_route')) {
    function to_route(string $name, array $parameters = []): RedirectResponse
    {
        return new RedirectResponse(route($name, $parameters));
    }
}

if (!function_exists('back')) {
    function back(): RedirectResponse
    {
        return new RedirectResponse(request()->previousUrl(url('/')));
    }
}

if (!function_exists('abort')) {
    function abort(int $status, string $message = ''): never
    {
        throw new HttpException($status, $message);
    }
}

if (!function_exists('csrf_token')) {
    function csrf_token(): string
    {
        return session()->token();
    }
}

if (!function_exists('csrf_field')) {
    function csrf_field(): string
    {
        return '<input type="hidden" name="_token" value="' . e(csrf_token()) . '">';
    }
}

if (!function_exists('method_field')) {
    function method_field(string $method): string
    {
        return '<input type="hidden" name="_method" value="' . e(strtoupper($method)) . '">';
    }
}

if (!function_exists('old')) {
    function old(string $key, mixed $default = ''): mixed
    {
        return session()->old($key, $default);
    }
}

if (!function_exists('error')) {
    /**
     * The first validation message for a field from the previous request.
     */
    function error(string $field): ?string
    {
        return session()->errors()[$field][0] ?? null;
    }
}
