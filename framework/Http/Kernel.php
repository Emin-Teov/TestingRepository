<?php

declare(strict_types=1);

namespace Framework\Http;

use Closure;
use Framework\Exceptions\Handler;
use Framework\Foundation\Application;
use Framework\Http\Middleware\Middleware;
use Framework\Routing\Router;
use Framework\Support\Config;
use InvalidArgumentException;
use Stringable;
use Throwable;

final class Kernel
{
    private const SECURITY_HEADERS = [
        'X-Content-Type-Options' => 'nosniff',
        'X-Frame-Options' => 'SAMEORIGIN',
        'Referrer-Policy' => 'strict-origin-when-cross-origin',
    ];

    public function __construct(
        private readonly Application $app,
        private readonly Router $router,
        private readonly Config $config,
    ) {
    }

    public function handle(Request $request): Response
    {
        $this->app->instance(Request::class, $request);

        try {
            $response = $this->pipeline($this->config->get('middleware.global', []), $request, $this->dispatchToRouter(...));
        } catch (Throwable $exception) {
            $response = $this->app->make(Handler::class)->render($request, $exception);
        }

        return $response->withHeaders(self::SECURITY_HEADERS);
    }

    private function dispatchToRouter(Request $request): Response
    {
        [$route, $parameters] = $this->router->match($request);
        $request->setRoute($route);

        return $this->pipeline(
            $this->expandMiddleware($route->getMiddleware()),
            $request,
            fn (Request $request) => $this->toResponse(
                $this->app->call($route->action(), [...$parameters, Request::class => $request]),
            ),
        );
    }

    /**
     * Pass the request through each middleware, finishing with the destination.
     *
     * @param list<string> $middleware "Class" or "Class:param1,param2"
     */
    private function pipeline(array $middleware, Request $request, Closure $destination): Response
    {
        $next = array_reduce(
            array_reverse($middleware),
            function (Closure $next, string $definition): Closure {
                [$class, $parameters] = array_pad(explode(':', $definition, 2), 2, '');

                return function (Request $request) use ($class, $parameters, $next): Response {
                    $instance = $this->app->make($class);

                    if (!$instance instanceof Middleware) {
                        throw new InvalidArgumentException("[$class] must implement " . Middleware::class);
                    }

                    return $instance->handle($request, $next, ...array_filter(explode(',', $parameters), 'strlen'));
                };
            },
            $destination,
        );

        return $next($request);
    }

    /**
     * Resolve group names ("web") and aliases ("role:admin") to middleware classes.
     */
    private function expandMiddleware(array $names): array
    {
        $groups = $this->config->get('middleware.groups', []);
        $aliases = $this->config->get('middleware.aliases', []);
        $resolved = [];

        foreach ($names as $name) {
            if (isset($groups[$name])) {
                array_push($resolved, ...$this->expandMiddleware($groups[$name]));
                continue;
            }

            [$alias, $parameters] = array_pad(explode(':', $name, 2), 2, null);
            $class = $aliases[$alias] ?? $alias;
            $resolved[] = $parameters === null ? $class : "$class:$parameters";
        }

        return $resolved;
    }

    private function toResponse(mixed $result): Response
    {
        return match (true) {
            $result instanceof Response => $result,
            is_array($result), $result instanceof \JsonSerializable => new JsonResponse($result),
            is_string($result), $result instanceof Stringable => new Response((string) $result, 200, ['Content-Type' => 'text/html; charset=utf-8']),
            $result === null => new Response('', 204),
            default => throw new InvalidArgumentException('Controllers must return a Response, array, string or null.'),
        };
    }
}
