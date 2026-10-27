<?php

return [
    'model' => App\Models\User::class,
    'username' => 'username',

    // Login throttling: lock out for `decay` seconds after `max_attempts` failures.
    'throttle' => [
        'max_attempts' => 5,
        'decay' => 60,
    ],
];
