<?php

declare(strict_types=1);

namespace Framework\Container;

use Closure;
use ReflectionClass;
use ReflectionFunction;
use ReflectionFunctionAbstract;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;

/**
 * A small dependency injection container with constructor autowiring.
 */
class Container
{
    private static ?Container $instance = null;

    /** @var array<string, array{factory: ?Closure, shared: bool}> */
    private array $bindings = [];

    /** @var array<string, object> */
    private array $instances = [];

    public static function getInstance(): static
    {
        return self::$instance ?? throw new ContainerException('The container has not been created yet.');
    }

    public static function setInstance(?Container $container): void
    {
        self::$instance = $container;
    }

    public function bind(string $id, ?Closure $factory = null, bool $shared = false): void
    {
        unset($this->instances[$id]);
        $this->bindings[$id] = ['factory' => $factory, 'shared' => $shared];
    }

    public function singleton(string $id, ?Closure $factory = null): void
    {
        $this->bind($id, $factory, shared: true);
    }

    public function instance(string $id, object $instance): void
    {
        $this->instances[$id] = $instance;
    }

    public function has(string $id): bool
    {
        return isset($this->instances[$id]) || isset($this->bindings[$id]);
    }

    /**
     * @template T of object
     * @param class-string<T>|string $id
     * @return T|mixed
     */
    public function make(string $id): mixed
    {
        if (isset($this->instances[$id])) {
            return $this->instances[$id];
        }

        $binding = $this->bindings[$id] ?? null;
        $factory = $binding['factory'] ?? null;
        $object = $factory !== null ? $factory($this) : $this->build($id);

        if ($binding['shared'] ?? false) {
            $this->instances[$id] = $object;
        }

        return $object;
    }

    public function build(string $class): object
    {
        if (!class_exists($class)) {
            throw new ContainerException("Class [$class] does not exist.");
        }

        $reflection = new ReflectionClass($class);

        if (!$reflection->isInstantiable()) {
            throw new ContainerException("[$class] is not instantiable.");
        }

        $constructor = $reflection->getConstructor();

        return $constructor === null
            ? new $class()
            : $reflection->newInstanceArgs($this->resolveParameters($constructor, []));
    }

    /**
     * Call a closure or [class, method] pair, injecting dependencies and named parameters.
     */
    public function call(callable|array $callback, array $parameters = []): mixed
    {
        if (is_array($callback) && is_string($callback[0])) {
            $callback[0] = $this->make($callback[0]);
        }

        $reflection = is_array($callback)
            ? new ReflectionMethod($callback[0], $callback[1])
            : new ReflectionFunction(Closure::fromCallable($callback));

        return $callback(...$this->resolveParameters($reflection, $parameters));
    }

    private function resolveParameters(ReflectionFunctionAbstract $function, array $given): array
    {
        return array_map(
            fn (ReflectionParameter $parameter) => $this->resolveParameter($parameter, $given),
            $function->getParameters(),
        );
    }

    private function resolveParameter(ReflectionParameter $parameter, array $given): mixed
    {
        $name = $parameter->getName();
        $type = $parameter->getType();
        $typeName = $type instanceof ReflectionNamedType ? $type->getName() : null;

        if ($typeName !== null && !$type->isBuiltin()) {
            if (isset($given[$typeName])) {
                return $given[$typeName];
            }

            if (isset($given[$name]) && $given[$name] instanceof $typeName) {
                return $given[$name];
            }

            if (!$parameter->isDefaultValueAvailable()) {
                return $this->make($typeName);
            }
        }

        if (array_key_exists($name, $given)) {
            return match ($typeName) {
                'int' => (int) $given[$name],
                'float' => (float) $given[$name],
                'bool' => filter_var($given[$name], FILTER_VALIDATE_BOOL),
                'string' => (string) $given[$name],
                default => $given[$name],
            };
        }

        if ($parameter->isDefaultValueAvailable()) {
            return $parameter->getDefaultValue();
        }

        if ($parameter->allowsNull()) {
            return null;
        }

        throw new ContainerException(sprintf(
            'Unable to resolve parameter $%s of %s().',
            $name,
            $parameter->getDeclaringFunction()->getName(),
        ));
    }
}
