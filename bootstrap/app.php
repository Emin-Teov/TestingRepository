<?php

declare(strict_types=1);

use Framework\Auth\Auth;
use Framework\Database\Connection;
use Framework\Database\Migrations\Migrator;
use Framework\Database\Schema\Schema;
use Framework\Foundation\Application;
use Framework\Logging\Logger;
use Framework\Routing\Router;
use Framework\Session\Session;
use Framework\Support\Config;
use Framework\Translation\Translator;
use Framework\View\View;

require __DIR__ . '/autoload.php';

$app = (new Application(dirname(__DIR__)))->bootstrap();

$app->singleton(Logger::class, fn (Application $app) => new Logger($app->storagePath('logs')));

$app->singleton(Connection::class, function (Application $app) {
    $config = $app->make(Config::class);
    $name = $config->get('database.default');

    return new Connection($config->get("database.connections.$name"), $app->basePath());
});

$app->singleton(Schema::class, fn (Application $app) => $app->make(Connection::class)->schema());

$app->singleton(Migrator::class, fn (Application $app) => new Migrator(
    $app->make(Connection::class),
    $app->databasePath('migrations'),
    $app->make(Config::class)->get('database.migrations', 'migrations'),
));

$app->singleton(Session::class, fn (Application $app) => new Session($app->make(Config::class)->get('app.session')));

$app->singleton(Translator::class, function (Application $app) {
    $config = $app->make(Config::class);

    return new Translator($app->langPath(), $config->get('app.locale'), $config->get('app.fallback_locale'));
});

$app->singleton(View::class, fn (Application $app) => new View($app->resourcePath('views')));

$app->singleton(Auth::class, fn (Application $app) => new Auth(
    $app->make(Session::class),
    $app->make(Config::class)->get('auth'),
));

$app->singleton(Router::class, function (Application $app) {
    $router = new Router();

    (require $app->basePath('routes/web.php'))($router);
    (require $app->basePath('routes/api.php'))($router);

    return $router;
});

return $app;
