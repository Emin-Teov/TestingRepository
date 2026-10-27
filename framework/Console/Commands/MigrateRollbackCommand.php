<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Database\Migrations\Migrator;
use Framework\Foundation\Application;

#[AsCommand('migrate:rollback', 'Roll back the last batch of migrations', '[--step=1] [--force]')]
final class MigrateRollbackCommand extends Command
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

        $steps = (int) $this->option('step', '1');
        $rolledBack = $this->migrator->rollback($steps, fn (string $name) => $this->line("  \033[33mRolled back\033[0m  $name"));

        $rolledBack === [] ? $this->info('Nothing to roll back.') : $this->info(sprintf('Rolled back %d migration(s).', count($rolledBack)));

        return self::SUCCESS;
    }
}
