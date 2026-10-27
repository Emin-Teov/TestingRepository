<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Closure;
use Framework\Auth\Auth;
use Framework\Http\Request;
use Framework\Http\Response;

final readonly class RedirectIfAuthenticated implements Middleware
{
    public function __construct(private Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        return $this->auth->check() ? to_route('home') : $next($request);
    }
}
