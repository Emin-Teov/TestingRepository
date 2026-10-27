<?php

declare(strict_types=1);

namespace Framework\Console;

use Framework\Support\Str;

/**
 * Base for the make:* commands, which write a new file from a stub.
 */
abstract class GeneratorCommand extends Command
{
    /**
     * @return array{path: string, contents: string}
     */
    abstract protected function build(string $name): array;

    public function handle(): int
    {
        $name = $this->argument(0);

        if ($name === null || !preg_match('/^[A-Za-z][A-Za-z0-9_\/]*$/', $name)) {
            $this->error('Please give a valid name, e.g. php console make:model Post');

            return self::FAILURE;
        }

        ['path' => $path, 'contents' => $contents] = $this->build($name);
        $file = $this->app->basePath($path);

        if (is_file($file)) {
            $this->error("$path already exists.");

            return self::FAILURE;
        }

        if (!is_dir(dirname($file))) {
            mkdir(dirname($file), 0775, true);
        }

        file_put_contents($file, $contents);
        $this->info("Created $path");

        return self::SUCCESS;
    }

    /**
     * Split "Admin/PostController" into ["App\Controllers\Admin", "PostController"].
     *
     * @return array{string, string}
     */
    protected function qualify(string $name, string $baseNamespace): array
    {
        $parts = array_map(Str::studly(...), explode('/', $name));
        $class = array_pop($parts);

        return [implode('\\', [$baseNamespace, ...$parts]), $class];
    }
}
