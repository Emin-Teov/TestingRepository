<?php

declare(strict_types=1);

namespace App\Controllers;

use Framework\Auth\Auth;
use Framework\Auth\RateLimiter;
use Framework\Http\RedirectResponse;
use Framework\Http\Request;
use Framework\Http\Response;
use Framework\Validation\ValidationException;

final readonly class AuthController
{
    public function __construct(private Auth $auth, private RateLimiter $limiter)
    {
    }

    public function create(): Response
    {
        return view('auth/login');
    }

    public function store(Request $request): RedirectResponse
    {
        $credentials = $request->validate([
            'username' => 'required|string|max:55',
            'password' => 'required|string|max:255',
        ]);

        $throttleKey = 'login|' . mb_strtolower($credentials['username']) . '|' . $request->ip();
        ['max_attempts' => $maxAttempts, 'decay' => $decay] = config('auth.throttle');

        if ($this->limiter->tooManyAttempts($throttleKey, $maxAttempts)) {
            throw new ValidationException(['username' => [__('auth.throttle', ['seconds' => $this->limiter->availableIn($throttleKey)])]]);
        }

        if (!$this->auth->attempt($credentials['username'], $credentials['password'])) {
            $this->limiter->hit($throttleKey, $decay);

            throw new ValidationException(['username' => [__('auth.failed')]]);
        }

        $this->limiter->clear($throttleKey);

        if (!$this->auth->user()->is_active) {
            $this->auth->logout();

            throw new ValidationException(['username' => [__('auth.inactive')]]);
        }

        // The intended URL was stored (already absolute) by the Authenticate middleware.
        return (new RedirectResponse(session()->pull('url.intended') ?? route('admin.dashboard')))
            ->with('success', __('auth.welcome', ['name' => $this->auth->user()->username]));
    }

    public function destroy(): RedirectResponse
    {
        $this->auth->logout();

        return to_route('home')->with('success', __('auth.logged_out'));
    }
}
