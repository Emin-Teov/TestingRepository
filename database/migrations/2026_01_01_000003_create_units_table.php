<?php

declare(strict_types=1);

use Framework\Database\Migrations\Migration;
use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\Schema;

return new class extends Migration {
    public function up(Schema $schema): void
    {
        // Units of measurement: ton, kilogram, piece, litre, ...
        $schema->create('units', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 55)->unique();
            $table->string('symbol', 15)->unique();
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('units');
    }
};
