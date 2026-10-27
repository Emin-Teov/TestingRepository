<?php

declare(strict_types=1);

use Framework\Database\Migrations\Migration;
use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\Schema;

return new class extends Migration {
    public function up(Schema $schema): void
    {
        $schema->create('suppliers', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('phone', 30)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('address')->nullable();
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('suppliers');
    }
};
