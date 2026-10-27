<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Closure;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Session\Session;

final readonly class StartSession implements Middleware
{
    public function __construct(private Session $session)
    {
    }

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        $this->session->start($request->isSecure());

        return $next($request);
    }
}
