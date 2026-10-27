<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Database\Seeder;

#[AsCommand('db:seed', 'Fill the database with initial data', '[--class=DatabaseSeeder]')]
final class SeedCommand extends Command
{
    public function handle(): int
    {
        $class = 'Database\\Seeders\\' . $this->option('class', 'DatabaseSeeder');

        if (!class_exists($class) || !is_subclass_of($class, Seeder::class)) {
            $this->error("Seeder [$class] not found.");

            return self::FAILURE;
        }

        $this->app->make($class)->run();
        $this->info('Database seeded.');

        return self::SUCCESS;
    }
}
