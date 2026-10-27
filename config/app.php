<?php

return [
    'name' => env('APP_NAME', 'Framework'),
    'env' => env('APP_ENV', 'production'),
    'debug' => (bool) env('APP_DEBUG', false),
    'url' => env('APP_URL', ''),
    'timezone' => env('APP_TIMEZONE', 'UTC'),
    // Shown next to prices and stock values.
    'currency' => env('APP_CURRENCY', 'AZN'),

    // Azerbaijani is the main language and English the second; missing translations fall back to English.
    'locale' => env('APP_LOCALE', 'az'),
    'fallback_locale' => 'en',
    // Every locale needs a lang/{code}.php file and a public/assets/flags/{code}.png icon.
    // The order here is the order of the flags in the top bar.
    'locales' => ['az' => 'Azərbaycan', 'en' => 'English', 'ru' => 'Русский'],

    'session' => [
        'name' => env('SESSION_NAME', 'framework_session'),
        'lifetime' => 0,
        'same_site' => 'Lax',
    ],

    'contact' => [
        'phone' => '+994775975319',
        'email' => 'e1000.tavakkulov@gmail.com',
        'linkedin' => 'https://www.linkedin.com/in/emin-tavakkulov-46718b237/',
    ],
];
