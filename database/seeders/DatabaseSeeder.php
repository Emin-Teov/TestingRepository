<?php

declare(strict_types=1);

namespace Database\Seeders;

use Framework\Database\Seeder;

final class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Required data first; DemoSeeder only adds sample records to try the application with.
        $this->call(UserTypeSeeder::class, AdminUserSeeder::class, UnitSeeder::class, DemoSeeder::class);
    }
}
