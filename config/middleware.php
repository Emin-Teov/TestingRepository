<?php

return [
    // Run on every request, before routing.
    'global' => [
        Framework\Http\Middleware\StartSession::class,
        Framework\Http\Middleware\SetLocale::class,
    ],

    'groups' => [
        'web' => [
            Framework\Http\Middleware\VerifyCsrfToken::class,
        ],
        'api' => [],
    ],

    'aliases' => [
        'auth' => Framework\Http\Middleware\Authenticate::class,
        'guest' => Framework\Http\Middleware\RedirectIfAuthenticated::class,
        'can' => App\Middleware\RequirePermission::class,
    ],
];
