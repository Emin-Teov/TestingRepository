<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Enums\Permission;
use App\Models\UserType;
use Framework\Database\Seeder;

/**
 * The built-in Administrator type plus typical warehouse roles. All of them can be changed
 * later under User types, except that the Administrator always keeps every permission.
 */
final class UserTypeSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            'Administrator' => ['Full access to everything, including users and permissions.', Permission::cases(), true],
            'Warehouse manager' => ['Runs the warehouse: entries, products, suppliers and reports.', [
                Permission::ViewEntries, Permission::CreateEntries, Permission::EditEntries, Permission::DeleteEntries,
                Permission::ManageProducts, Permission::ManageSuppliers, Permission::ViewReports,
            ], false],
            'Storekeeper' => ['Receives goods and records stock entries.', [
                Permission::ViewEntries, Permission::CreateEntries,
            ], false],
            'Accountant' => ['Read-only access to entries and the stock report.', [
                Permission::ViewEntries, Permission::ViewReports,
            ], false],
        ];

        foreach ($types as $name => [$description, $permissions, $locked]) {
            if (UserType::query()->where('name', $name)->exists()) {
                continue;
            }

            $columns = [];

            foreach (Permission::cases() as $permission) {
                $columns[$permission->column()] = in_array($permission, $permissions, true);
            }

            (new UserType())->forceFill(['name' => $name, 'description' => $description, 'is_locked' => $locked, ...$columns])->save();
        }
    }
}
