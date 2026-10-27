<?php

declare(strict_types=1);

namespace Framework\Console\Commands;

use Framework\Console\AsCommand;
use Framework\Console\Command;
use Framework\Database\Migrations\Migrator;
use Framework\Foundation\Application;

#[AsCommand('migrate', 'Run the pending database migrations', '[--seed] [--pretend]')]
final class MigrateCommand extends Command
{
    public function __construct(Application $app, private readonly Migrator $migrator)
    {
        parent::__construct($app);
    }

    public function handle(): int
    {
        if ($this->option('pretend')) {
            return $this->pretend();
        }

        $ran = $this->migrator->run(fn (string $name) => $this->line("  \033[32mMigrated\033[0m  $name"));

        $ran === [] ? $this->info('Nothing to migrate.') : $this->info(sprintf('Ran %d migration(s).', count($ran)));

        if ($this->option('seed')) {
            return $this->app->make(SeedCommand::class)->handle();
        }

        return self::SUCCESS;
    }

    /**
     * Print the SQL instead of running it, e.g. to paste into phpMyAdmin on hosting without SSH.
     */
    private function pretend(): int
    {
        foreach ($this->migrator->pretend() as $name => $queries) {
            $this->comment("-- $name");
            array_map(fn (string $sql) => $this->line($sql . ';'), $queries);
            $this->line();
        }

        return self::SUCCESS;
    }
}
