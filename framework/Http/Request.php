<?php

declare(strict_types=1);

namespace Framework\Http;

use Framework\Routing\Route;
use Framework\Support\Arr;
use Framework\Validation\Factory as ValidationFactory;

final class Request
{
    private const SPOOFABLE_METHODS = ['PUT', 'PATCH', 'DELETE'];

    private ?array $json = null;

    private ?Route $route = null;

    public function __construct(
        public readonly string $method,
        public readonly string $path,
        private readonly array $query = [],
        private readonly array $post = [],
        private readonly array $server = [],
        public readonly string $basePath = '',
        private readonly string $content = '',
    ) {
    }

    public static function capture(): self
    {
        $server = $_SERVER;
        $method = strtoupper($server['REQUEST_METHOD'] ?? 'GET');

        // HTML forms can only send GET/POST; a hidden "_method" field selects PUT, PATCH or DELETE.
        if ($method === 'POST' && in_array(strtoupper((string) ($_POST['_method'] ?? '')), self::SPOOFABLE_METHODS, true)) {
            $method = strtoupper($_POST['_method']);
        }

        $basePath = self::detectBasePath($server);
        $uri = parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';

        if ($basePath !== '' && str_starts_with($uri, $basePath)) {
            $uri = substr($uri, strlen($basePath));
        }

        return new self(
            method: $method,
            path: self::normalizePath(rawurldecode($uri)),
            query: $_GET,
            post: $_POST,
            server: $server,
            basePath: $basePath,
            content: (string) file_get_contents('php://input'),
        );
    }

    public static function create(string $method, string $path, array $parameters = [], array $server = []): self
    {
        $method = strtoupper($method);
        $query = $method === 'GET' ? $parameters : [];
        $post = $method === 'GET' ? [] : $parameters;

        return new self($method, self::normalizePath($path), $query, $post, $server + ['REQUEST_METHOD' => $method]);
    }

    /**
     * The URL prefix of the app, e.g. "/Framework" when served from http://localhost/Framework/.
     */
    private static function detectBasePath(array $server): string
    {
        $uri = parse_url($server['REQUEST_URI'] ?? '/', PHP_URL_PATH) ?: '/';
        $base = rtrim(str_replace('\\', '/', dirname($server['SCRIPT_NAME'] ?? '/')), '/');

        // The root .htaccess rewrites into public/ internally, so "/public" is not part of the visible URL.
        if (str_ends_with($base, '/public') && !str_starts_with($uri, $base . '/') && $uri !== $base) {
            $base = substr($base, 0, -strlen('/public'));
        }

        return $base;
    }

    private static function normalizePath(string $path): string
    {
        return '/' . trim($path, '/');
    }

    public function setRoute(Route $route): void
    {
        $this->route = $route;
    }

    public function route(): ?Route
    {
        return $this->route;
    }

    public function isMethod(string $method): bool
    {
        return $this->method === strtoupper($method);
    }

    /**
     * All input: query string, form fields and JSON body (later sources win).
     */
    public function all(): array
    {
        return array_replace($this->query, $this->post, $this->json());
    }

    public function input(string $key, mixed $default = null): mixed
    {
        return Arr::get($this->all(), $key, $default);
    }

    public function string(string $key, string $default = ''): string
    {
        $value = $this->input($key, $default);

        return is_scalar($value) ? trim((string) $value) : $default;
    }

    public function integer(string $key, int $default = 0): int
    {
        return filter_var($this->input($key), FILTER_VALIDATE_INT) ?: $default;
    }

    public function query(string $key, mixed $default = null): mixed
    {
        return $this->query[$key] ?? $default;
    }

    public function queryAll(): array
    {
        return $this->query;
    }

    public function only(string ...$keys): array
    {
        return Arr::only($this->all(), $keys);
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function json(): array
    {
        if ($this->json === null) {
            $decoded = str_contains($this->header('Content-Type', ''), 'application/json')
                ? json_decode($this->content, true)
                : null;

            $this->json = is_array($decoded) ? $decoded : [];
        }

        return $this->json;
    }

    public function header(string $name, ?string $default = null): ?string
    {
        $key = strtoupper(str_replace('-', '_', $name));

        return $this->server["HTTP_$key"] ?? $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        return (string) ($this->server['REMOTE_ADDR'] ?? '127.0.0.1');
    }

    public function isSecure(): bool
    {
        return ($this->server['HTTPS'] ?? 'off') !== 'off' || ($this->server['SERVER_PORT'] ?? null) == 443;
    }

    public function expectsJson(): bool
    {
        return str_starts_with($this->path, '/api')
            || str_contains((string) $this->header('Accept'), 'application/json')
            || $this->header('X-Requested-With') === 'XMLHttpRequest';
    }

    /**
     * The referring page when it belongs to this site, otherwise the fallback.
     */
    public function previousUrl(string $fallback): string
    {
        $referer = $this->header('Referer');

        if ($referer === null) {
            return $fallback;
        }

        $host = parse_url($referer, PHP_URL_HOST);
        $port = parse_url($referer, PHP_URL_PORT);

        if ($host !== null && ($port !== null ? "$host:$port" : $host) === ($this->server['HTTP_HOST'] ?? null)) {
            return $referer;
        }

        return $fallback;
    }

    /**
     * Validate the input against the rules, returning only the validated fields.
     *
     * @throws \Framework\Validation\ValidationException
     */
    public function validate(array $rules): array
    {
        return app(ValidationFactory::class)->make($this->all(), $rules)->validate();
    }
}
