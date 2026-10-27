<?php

declare(strict_types=1);

use Framework\Database\Migrations\Migration;
use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\Schema;

return new class extends Migration {
    public function up(Schema $schema): void
    {
        $schema->create('products', function (Blueprint $table): void {
            $table->id();
            $table->string('sku', 55)->unique();
            $table->string('name', 100)->index();
            $table->foreignId('category_id')->nullable()->constrained('categories')->nullOnDelete();
            // Every quantity of a product is counted in its unit, so a unit in use cannot be deleted.
            $table->foreignId('unit_id')->constrained('units');
            $table->text('description')->nullable();
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('products');
    }
};
