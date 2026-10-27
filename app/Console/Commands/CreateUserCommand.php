<?php

declare(strict_types=1);

namespace App\Console\Commands;

use App\Models\User;
use App\Models\UserType;
use Framework\Auth\Hash;
use Framework\Console\AsCommand;
use Framework\Console\Command;

#[AsCommand('user:create', 'Create a user', '<username> <password> [--type="User type name"]')]
final class CreateUserCommand extends Command
{
    public function handle(): int
    {
        $username = $this->argument(0);
        $password = $this->argument(1);
        $typeName = (string) $this->option('type', 'Administrator');
        $type = UserType::query()->where('name', $typeName)->first();

        if ($username === null || $password === null) {
            $this->error('Usage: php console user:create <username> <password> [--type="User type name"]');

            return self::FAILURE;
        }

        if ($type === null) {
            $names = array_map(static fn (UserType $type) => $type->name, UserType::query()->orderBy('name')->get());
            $this->error("User type \"$typeName\" does not exist. Available: " . implode(', ', $names));

            return self::FAILURE;
        }

        if (mb_strlen($password) < 8) {
            $this->error('The password must be at least 8 characters.');

            return self::FAILURE;
        }

        if (User::query()->where('username', $username)->exists()) {
            $this->error("User \"$username\" already exists.");

            return self::FAILURE;
        }

        User::create(['username' => $username, 'password' => Hash::make($password), 'usertype_id' => $type->id, 'is_active' => true]);
        $this->info("Created {$type->name} \"$username\".");

        return self::SUCCESS;
    }
}
