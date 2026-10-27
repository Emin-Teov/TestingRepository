<?php

declare(strict_types=1);

use Framework\Database\Migrations\Migration;
use Framework\Database\Schema\Blueprint;
use Framework\Database\Schema\Schema;

return new class extends Migration {
    public function up(Schema $schema): void
    {
        // One row per kind of user (Administrator, Storekeeper, ...). Every permission is a column,
        // so the rights of each type can be read and changed directly in the database as well.
        $schema->create('usertypes', function (Blueprint $table): void {
            $table->id();
            $table->string('name', 55)->unique();
            $table->string('description')->nullable();
            $table->boolean('can_view_entries')->default(false);
            $table->boolean('can_create_entries')->default(false);
            $table->boolean('can_edit_entries')->default(false);
            $table->boolean('can_delete_entries')->default(false);
            $table->boolean('can_manage_products')->default(false);
            $table->boolean('can_manage_suppliers')->default(false);
            $table->boolean('can_view_reports')->default(false);
            $table->boolean('can_manage_users')->default(false);
            $table->boolean('can_manage_usertypes')->default(false);
            // Locked types (the built-in Administrator) always have every permission and cannot be deleted.
            $table->boolean('is_locked')->default(false);
            $table->timestamps();
        });
    }

    public function down(Schema $schema): void
    {
        $schema->dropIfExists('usertypes');
    }
};
