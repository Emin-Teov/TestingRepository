<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\User;
use App\Models\UserType;
use Framework\Auth\Hash;
use Framework\Database\Seeder;

/**
 * Creates the first administrator from ADMIN_USERNAME / ADMIN_PASSWORD in .env.
 * When no password is set, a random one is generated and printed once.
 */
final class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $username = (string) env('ADMIN_USERNAME', 'admin');

        if (User::query()->where('username', $username)->exists()) {
            fwrite(STDOUT, "  Admin user \"$username\" already exists, skipped." . PHP_EOL);

            return;
        }

        $password = (string) env('ADMIN_PASSWORD', '');
        $generated = $password === '';

        if ($generated) {
            $password = bin2hex(random_bytes(8));
        }

        $type = UserType::query()->where('is_locked', true)->orderBy('id')->first()
            ?? throw new \RuntimeException('Run UserTypeSeeder first: there is no Administrator user type.');

        User::create([
            'usertype_id' => $type->id,
            'username' => $username,
            'full_name' => 'Administrator',
            'password' => Hash::make($password),
            'is_active' => true,
        ]);

        fwrite(STDOUT, "  Created admin user \"$username\"" . ($generated ? " with password: $password" : '') . PHP_EOL);
    }
}
