<?php

declare(strict_types=1);

namespace App\Middleware;

use App\Enums\Permission;
use App\Models\User;
use Closure;
use Framework\Auth\Auth;
use Framework\Exceptions\HttpException;
use Framework\Http\Middleware\Middleware;
use Framework\Http\Request;
use Framework\Http\Response;

/**
 * Usage in routes: ->middleware('can:manage_users') or 'can:view_entries,create_entries' (all are required).
 * Without parameters it only checks that the account is active.
 */
final readonly class RequirePermission implements Middleware
{
    public function __construct(private Auth $auth)
    {
    }

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        $user = $this->auth->user();

        if (!$user instanceof User || !$user->is_active) {
            $this->auth->logout();

            return to_route('login')->with('error', __('auth.inactive'));
        }

        if (!$user->can(...array_map(Permission::from(...), $parameters))) {
            throw new HttpException(403);
        }

        return $next($request);
    }
}
