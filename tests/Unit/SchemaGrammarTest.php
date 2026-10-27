<?php

declare(strict_types=1);

namespace Tests\Unit;

use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\Grammars\MySqlGrammar;
use Framework\Database\Schema\Grammars\SqliteGrammar;
use PHPUnit\Framework\TestCase;

final class SchemaGrammarTest extends TestCase
{
    private function blueprint(): Blueprint
    {
        $table = new Blueprint('items');
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
        $table->string('value', 55)->unique();
        $table->boolean('active')->default(true);
        $table->date('received_on');
        $table->dateTime('created_at')->nullable()->index();

        return $table;
    }

    public function test_mysql(): void
    {
        [$create, $index] = (new MySqlGrammar())->compileCreate($this->blueprint());

        $this->assertStringContainsString('`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY', $create);
        $this->assertStringContainsString('`user_id` BIGINT UNSIGNED NULL', $create);
        $this->assertStringContainsString('`received_on` DATE NOT NULL', $create);
        $this->assertStringContainsString('`active` TINYINT(1) NOT NULL DEFAULT 1', $create);
        $this->assertStringContainsString('CONSTRAINT `items_value_unique` UNIQUE (`value`)', $create);
        $this->assertStringContainsString('FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL', $create);
        $this->assertStringEndsWith('ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci', $create);
        $this->assertSame('CREATE INDEX `items_created_at_index` ON `items` (`created_at`)', $index);
    }

    public function test_sqlite(): void
    {
        [$create] = (new SqliteGrammar())->compileCreate($this->blueprint());

        $this->assertStringContainsString('"id" INTEGER PRIMARY KEY AUTOINCREMENT', $create);
        $this->assertStringContainsString('"value" VARCHAR(55) NOT NULL', $create);
        $this->assertStringContainsString('"active" INTEGER NOT NULL DEFAULT 1', $create);
        $this->assertStringContainsString('"received_on" DATE NOT NULL', $create);
    }
}
