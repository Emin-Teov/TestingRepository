<?php

declare(strict_types=1);

namespace Framework\Database\Migrations;

use Closure;
use Framework\Database\Connection;
use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\Schema;
use UnexpectedValueException;

/**
 * Runs the files in database/migrations in filename order and records each one in the
 * migrations table, grouped into "batches" so a whole run can be rolled back together.
 */
final readonly class Migrator
{
    private Schema $schema;

    public function __construct(
        private Connection $db,
        private string $path,
        private string $table = 'migrations',
    ) {
        $this->schema = $db->schema();
    }

    /**
     * @return array<string, string> Migration name => file path, in run order.
     */
    public function files(): array
    {
        $files = [];

        foreach (glob($this->path . '/*.php') ?: [] as $file) {
            $files[basename($file, '.php')] = $file;
        }

        ksort($files);

        return $files;
    }

    /**
     * @return array<string, int> Migration name => batch number.
     */
    public function ran(): array
    {
        if (!$this->schema->hasTable($this->table)) {
            return [];
        }

        $rows = $this->db->table($this->table)->orderBy('id')->get();

        return array_map(intval(...), array_column($rows, 'batch', 'migration'));
    }

    /**
     * @return list<string>
     */
    public function pending(): array
    {
        return array_values(array_diff(array_keys($this->files()), array_keys($this->ran())));
    }

    /**
     * @return list<array{name: string, ran: bool, batch: ?int}>
     */
    public function status(): array
    {
        $ran = $this->ran();

        return array_map(
            static fn (string $name) => ['name' => $name, 'ran' => isset($ran[$name]), 'batch' => $ran[$name] ?? null],
            array_keys($this->files()),
        );
    }

    /**
     * Run all pending migrations as a new batch.
     *
     * @param Closure(string): void|null $onMigrated Called with each migration name.
     * @return list<string> The migrations that ran.
     */
    public function run(?Closure $onMigrated = null): array
    {
        $pending = $this->pending();

        if ($pending === []) {
            return [];
        }

        $this->ensureRepositoryExists();
        $batch = max([0, ...array_values($this->ran())]) + 1;
        $files = $this->files();

        foreach ($pending as $name) {
            $this->resolve($files[$name])->up($this->schema);
            $this->db->table($this->table)->insert(['migration' => $name, 'batch' => $batch]);

            if ($onMigrated !== null) {
                $onMigrated($name);
            }
        }

        return $pending;
    }

    /**
     * The SQL the pending migrations would execute, without running anything.
     *
     * @return array<string, list<string>>
     */
    public function pretend(): array
    {
        $files = $this->files();
        $queries = [];

        foreach ($this->pending() as $name) {
            $queries[$name] = $this->db->pretend(fn () => $this->resolve($files[$name])->up($this->schema));
        }

        return $queries;
    }

    /**
     * Roll back the last $steps batches.
     *
     * @return list<string> The migrations that were rolled back.
     */
    public function rollback(int $steps = 1, ?Closure $onRolledBack = null): array
    {
        $ran = $this->ran();

        if ($ran === []) {
            return [];
        }

        $batches = array_slice(array_unique(array_values($ran)), -max(1, $steps));
        $names = array_reverse(array_keys(array_filter($ran, static fn (int $batch) => in_array($batch, $batches, true))));

        return $this->down($names, $onRolledBack);
    }

    /**
     * Roll back every migration.
     *
     * @return list<string>
     */
    public function reset(?Closure $onRolledBack = null): array
    {
        return $this->down(array_reverse(array_keys($this->ran())), $onRolledBack);
    }

    /**
     * Drop every table (without calling down()) and run all migrations again.
     *
     * @return list<string>
     */
    public function fresh(?Closure $onMigrated = null): array
    {
        $this->schema->dropAllTables();

        return $this->run($onMigrated);
    }

    private function down(array $names, ?Closure $callback): array
    {
        $files = $this->files();

        foreach ($names as $name) {
            if (!isset($files[$name])) {
                throw new UnexpectedValueException("Migration file for [$name] is missing.");
            }

            $this->resolve($files[$name])->down($this->schema);
            $this->db->table($this->table)->where('migration', $name)->delete();

            if ($callback !== null) {
                $callback($name);
            }
        }

        return $names;
    }

    private function ensureRepositoryExists(): void
    {
        if ($this->schema->hasTable($this->table)) {
            return;
        }

        $this->schema->create($this->table, function (Blueprint $table): void {
            $table->id();
            $table->string('migration')->unique();
            $table->integer('batch');
        });
    }

    private function resolve(string $file): Migration
    {
        $migration = require $file;

        if (!$migration instanceof Migration) {
            throw new UnexpectedValueException("[$file] must return an instance of " . Migration::class);
        }

        return $migration;
    }
}
