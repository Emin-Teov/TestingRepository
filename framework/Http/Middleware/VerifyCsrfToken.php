<?php

declare(strict_types=1);

namespace Framework\Http\Middleware;

use Closure;
use Framework\Exceptions\HttpException;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Session\Session;

final readonly class VerifyCsrfToken implements Middleware
{
    private const SAFE_METHODS = ['GET', 'HEAD', 'OPTIONS'];

    public function __construct(private Session $session)
    {
    }

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        if (!in_array($request->method, self::SAFE_METHODS, true)) {
            $token = $request->input('_token') ?? $request->header('X-CSRF-TOKEN');

            if (!is_string($token) || !hash_equals($this->session->token(), $token)) {
                throw new HttpException(419, __('errors.419'));
            }
        }

        return $next($request);
    }
}
