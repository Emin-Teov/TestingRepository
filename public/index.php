<?php

declare(strict_types=1);

use Framework\Http\Kernel;
use Framework\Http\Request;

// Let PHP's built-in server (`php console serve`) deliver static assets itself.
if (PHP_SAPI === 'cli-server' && is_file(__DIR__ . parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH))) {
    return false;
}

$app = require dirname(__DIR__) . '/bootstrap/app.php';

$app->make(Kernel::class)
    ->handle(Request::capture())
    ->send();
