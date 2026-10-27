<?php

declare(strict_types=1);

namespace Framework\Routing;

use Closure;

final class Route
{
    /** "{name}" or "{name:pattern}"; the pattern may itself contain quantifiers such as "{2}". */
    private const PLACEHOLDER = '/\{(\w+)(?::((?:[^{}]+|\{[^{}]*\})+))?\}/';

    private ?string $name = null;

    private ?string $regex = null;

    /**
     * @param list<string> $methods
     * @param Closure|array{class-string, string} $action
     * @param list<string> $middleware
     */
    public function __construct(
        private readonly array $methods,
        private readonly string $uri,
        private readonly Closure|array $action,
        private array $middleware = [],
        private readonly string $namePrefix = '',
    ) {
    }

    public function name(string $name): self
    {
        $this->name = $this->namePrefix . $name;

        return $this;
    }

    public function middleware(string ...$middleware): self
    {
        array_push($this->middleware, ...$middleware);

        return $this;
    }

    /**
     * @return list<string>
     */
    public function getMiddleware(): array
    {
        return $this->middleware;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function methods(): array
    {
        return $this->methods;
    }

    public function action(): Closure|array
    {
        return $this->action;
    }

    public function allowsMethod(string $method): bool
    {
        return in_array($method, $this->methods, true) || ($method === 'HEAD' && in_array('GET', $this->methods, true));
    }

    /**
     * Match the path and return the route parameters, or null when it does not match.
     *
     * Placeholders: "{id}" matches one segment, "{id:\d+}" uses a custom pattern.
     */
    public function matches(string $path): ?array
    {
        $this->regex ??= '#^' . preg_replace_callback(
            self::PLACEHOLDER,
            static fn (array $m) => '(?P<' . $m[1] . '>' . ($m[2] ?? '[^/]+') . ')',
            $this->uri,
        ) . '$#u';

        if (!preg_match($this->regex, $path, $matches)) {
            return null;
        }

        return array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
    }

    public function path(array $parameters = []): string
    {
        return (string) preg_replace_callback(self::PLACEHOLDER, static function (array $m) use ($parameters): string {
            if (!array_key_exists($m[1], $parameters)) {
                throw new \InvalidArgumentException("Missing parameter [{$m[1]}] for route [{$m[0]}].");
            }

            return rawurlencode((string) $parameters[$m[1]]);
        }, $this->uri);
    }
}
