<?php

declare(strict_types=1);

namespace Framework\Database\Migrations;

use Framework\Database\Schema\Schema;

/**
 * Each file in database/migrations returns an anonymous subclass:
 *
 *   return new class extends Migration {
 *       public function up(Schema $schema): void { ... }
 *       public function down(Schema $schema): void { ... }
 *   };
 */
abstract class Migration
{
    abstract public function up(Schema $schema): void;

    public function down(Schema $schema): void
    {
    }
}
