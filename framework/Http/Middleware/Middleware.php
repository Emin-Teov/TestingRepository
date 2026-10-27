<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Closure;
use Framework\Http\Request;
use Framework\Http\Response;

interface Middleware
{
    /**
     * @param Closure(Request): Response $next
     * @param string ...$parameters Values after the colon in the route definition, e.g. "role:admin,editor".
     */
    public function handle(Request $request, Closure $next, string ...$parameters): Response;
}
