<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Closure;
use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Routing\Route;
use Framework\Routing\Router;

#[AsCommand('route:list', 'List all registered routes')]
final class RouteListCommand extends Command
{
    public function handle(): int
    {
        $rows = array_map(static function (Route $route): array {
            $action = $route->action();

            return [
                implode('|', $route->methods()),
                $route->uri(),
                $route->getName() ?? '',
                $action instanceof Closure ? 'Closure' : basename(str_replace('\\', '/', $action[0])) . '@' . $action[1],
                implode(', ', $route->getMiddleware()),
            ];
        }, $this->app->make(Router::class)->routes());

        $this->table(['Method', 'URI', 'Name', 'Action', 'Middleware'], $rows);

        return self::SUCCESS;
    }
}
