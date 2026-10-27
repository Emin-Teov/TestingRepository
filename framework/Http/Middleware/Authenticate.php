<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Closure;
use Framework\Auth\Auth;
use Framework\Exceptions\HttpException;
use Framework\Http\Request;
use Framework\Http\Response;

final readonly class Authenticate implements Middleware
{
    public function __construct(private Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        if ($this->auth->check()) {
            return $next($request);
        }

        if ($request->expectsJson()) {
            throw new HttpException(401, __('errors.401'));
        }

        session()->put('url.intended', url($request->path, $request->queryAll()));

        return to_route('login')->with('warning', __('auth.required'));
    }
}
