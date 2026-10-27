<?php

declare(strict_types=1);

namespace Framework\Database;

use Framework\Container\Container;

/**
 * Seeders fill the database with initial or demo data: `php console db:seed`.
 */
abstract class Seeder
{
    public function __construct(protected readonly Container $container, protected readonly Connection $db)
    {
    }

    abstract public function run(): void;

    /**
     * Run other seeders.
     *
     * @param class-string<Seeder> ...$seeders
     */
    protected function call(string ...$seeders): void
    {
        foreach ($seeders as $seeder) {
            $this->container->make($seeder)->run();
        }
    }
}
