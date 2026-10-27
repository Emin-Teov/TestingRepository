<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Database\Migrations\Migrator;
use Framework\Foundation\Application;

#[AsCommand('migrate:fresh', 'Drop all tables and run every migration again', '[--seed] [--force]')]
final class MigrateFreshCommand extends Command
{
    public function __construct(Application $app, private readonly Migrator $migrator)
    {
        parent::__construct($app);
    }

    public function handle(): int
    {
        if (!$this->confirmProduction()) {
            return self::FAILURE;
        }

        $this->comment('Dropped all tables.');
        $this->migrator->fresh(fn (string $name) => $this->line("  \033[32mMigrated\033[0m  $name"));

        if ($this->option('seed')) {
            return $this->app->make(SeedCommand::class)->handle();
        }

        return self::SUCCESS;
    }
}
