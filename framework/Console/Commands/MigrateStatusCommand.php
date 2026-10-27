<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Database\Migrations\Migrator;
use Framework\Foundation\Application;

#[AsCommand('migrate:status', 'Show which migrations have run')]
final class MigrateStatusCommand extends Command
{
    public function __construct(Application $app, private readonly Migrator $migrator)
    {
        parent::__construct($app);
    }

    public function handle(): int
    {
        $rows = array_map(
            static fn (array $m) => [$m['name'], $m['ran'] ? 'Ran' : 'Pending', $m['batch'] ?? ''],
            $this->migrator->status(),
        );

        $rows === [] ? $this->comment('No migrations found.') : $this->table(['Migration', 'Status', 'Batch'], $rows);

        return self::SUCCESS;
    }
}
