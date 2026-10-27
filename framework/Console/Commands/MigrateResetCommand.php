<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Database\Migrations\Migrator;
use Framework\Foundation\Application;

#[AsCommand('migrate:reset', 'Roll back all database migrations', '[--force]')]
final class MigrateResetCommand extends Command
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

        $rolledBack = $this->migrator->reset(fn (string $name) => $this->line("  \033[33mRolled back\033[0m  $name"));
        $this->info(sprintf('Rolled back %d migration(s).', count($rolledBack)));

        return self::SUCCESS;
    }
}
