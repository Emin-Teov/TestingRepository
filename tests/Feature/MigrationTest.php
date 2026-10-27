<?php

declare(strict_types=1);

namespace Tests\Feature;

use Framework\Database\Migrations\Migrator;
use Framework\Database\Schema\Schema;
use Tests\TestCase;

final class MigrationTest extends TestCase
{
    public function test_migrations_ran_in_setup_and_can_be_rolled_back(): void
    {
        $migrator = $this->app->make(Migrator::class);
        $schema = $this->app->make(Schema::class);

        $this->assertSame([], $migrator->pending());
        $this->assertTrue($schema->hasTable('users'));
        $this->assertTrue($schema->hasTable('usertypes'));
        $this->assertTrue($schema->hasTable('stock_entries'));

        $rolledBack = $migrator->rollback();

        $this->assertCount(7, $rolledBack);
        $this->assertSame('2026_01_01_000007_create_stock_entries_table', $rolledBack[0]);
        $this->assertFalse($schema->hasTable('usertypes'));
        $this->assertCount(7, $migrator->pending());

        $migrator->run();
        $this->assertSame([], $migrator->pending());
        $this->assertSame([1], array_values(array_unique(array_column($migrator->status(), 'batch'))));
    }

    public function test_pretend_returns_sql_without_running_it(): void
    {
        $migrator = $this->app->make(Migrator::class);
        $migrator->rollback();

        $sql = $migrator->pretend();

        $this->assertStringStartsWith('CREATE TABLE "usertypes"', $sql['2026_01_01_000001_create_usertypes_table'][0]);
        $this->assertFalse($this->app->make(Schema::class)->hasTable('users'));
    }

    public function test_fresh_drops_everything_and_migrates_again(): void
    {
        $migrator = $this->app->make(Migrator::class);

        $this->assertCount(7, $migrator->fresh());
        $this->assertSame([], $migrator->pending());
    }
}
