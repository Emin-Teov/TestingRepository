<?php

declare(strict_types=1);

// Prefer Composer's autoloader; fall back to a minimal PSR-4 loader so the
// project also runs on hosting where `composer install` is not available.
$composer = dirname(__DIR__) . '/vendor/autoload.php';

if (is_file($composer)) {
    require $composer;

    return;
}

spl_autoload_register(static function (string $class): void {
    $prefixes = [
        'App\\' => 'app/',
        'Framework\\' => 'framework/',
        'Database\\Seeders\\' => 'database/seeders/',
        'Tests\\' => 'tests/',
    ];

    foreach ($prefixes as $prefix => $directory) {
        if (str_starts_with($class, $prefix)) {
            $file = dirname(__DIR__) . '/' . $directory . str_replace('\\', '/', substr($class, strlen($prefix))) . '.php';

            if (is_file($file)) {
                require $file;
            }

            return;
        }
    }
});

require dirname(__DIR__) . '/framework/helpers.php';
