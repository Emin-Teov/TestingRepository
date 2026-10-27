<?php

declare(strict_types=1);

namespace Framework\Console;

use Framework\Foundation\Application;
use ReflectionClass;
use Throwable;

/**
 * Entry point for `php console <command>`. Commands are discovered in framework/Console/Commands
 * and app/Console/Commands by their #[AsCommand] attribute.
 */
final class Kernel
{
    private const DIRECTORIES = [
        'Framework\\Console\\Commands\\' => 'framework/Console/Commands',
        'App\\Console\\Commands\\' => 'app/Console/Commands',
    ];

    /** @var array<string, array{class: class-string<Command>, attribute: AsCommand}>|null */
    private ?array $commands = null;

    public function __construct(private readonly Application $app)
    {
    }

    public function handle(array $argv): int
    {
        if (function_exists('sapi_windows_vt100_support')) {
            @sapi_windows_vt100_support(STDOUT, true);
            @sapi_windows_vt100_support(STDERR, true);
        }

        $name = $argv[1] ?? 'list';

        if (in_array($name, ['list', 'help', '--help', '-h'], true)) {
            $this->printList();

            return Command::SUCCESS;
        }

        $definition = $this->commands()[$name] ?? null;

        if ($definition === null) {
            fwrite(STDERR, "\033[31mCommand \"$name\" is not defined.\033[0m" . PHP_EOL);
            $this->printList();

            return Command::FAILURE;
        }

        try {
            /** @var Command $command */
            $command = $this->app->make($definition['class']);
            $command->setInput(array_slice($argv, 2));

            return $command->handle();
        } catch (Throwable $exception) {
            fwrite(STDERR, "\033[31m" . $exception::class . ': ' . $exception->getMessage() . "\033[0m" . PHP_EOL);

            if ($this->app->isDebug()) {
                fwrite(STDERR, $exception->getTraceAsString() . PHP_EOL);
            }

            return Command::FAILURE;
        }
    }

    /**
     * @return array<string, array{class: class-string<Command>, attribute: AsCommand}>
     */
    public function commands(): array
    {
        if ($this->commands !== null) {
            return $this->commands;
        }

        $this->commands = [];

        foreach (self::DIRECTORIES as $namespace => $directory) {
            foreach (glob($this->app->basePath($directory) . '/*.php') ?: [] as $file) {
                $class = $namespace . basename($file, '.php');

                if (!class_exists($class) || !is_subclass_of($class, Command::class)) {
                    continue;
                }

                $attributes = (new ReflectionClass($class))->getAttributes(AsCommand::class);

                if ($attributes !== []) {
                    $attribute = $attributes[0]->newInstance();
                    $this->commands[$attribute->name] = ['class' => $class, 'attribute' => $attribute];
                }
            }
        }

        ksort($this->commands);

        return $this->commands;
    }

    private function printList(): void
    {
        $lines = ['', "\033[32mFramework\033[0m " . Application::VERSION, '', "\033[33mUsage:\033[0m", '  php console <command> [arguments] [--options]', '', "\033[33mAvailable commands:\033[0m"];
        $width = max(array_map(strlen(...), array_keys($this->commands()))) + 2;
        $group = null;

        $names = array_keys($this->commands());

        foreach ($this->commands() as $name => ['attribute' => $attribute]) {
            // "migrate" and "migrate:fresh" share the "migrate" heading; lone commands get none.
            $prefix = explode(':', $name)[0];
            $isGroup = array_filter($names, static fn (string $other) => str_starts_with($other, "$prefix:")) !== [];

            if ($prefix !== $group && $isGroup) {
                $lines[] = " \033[33m$prefix\033[0m";
            }

            $group = $prefix;
            $lines[] = sprintf("  \033[32m%-{$width}s\033[0m%s", $name, $attribute->description);
        }

        fwrite(STDOUT, implode(PHP_EOL, $lines) . PHP_EOL . PHP_EOL);
    }
}
