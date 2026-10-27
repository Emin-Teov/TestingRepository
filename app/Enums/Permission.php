<?php

declare(strict_types=1);

namespace App\Enums;

/**
 * Every permission is stored as a "can_{value}" boolean column on the usertypes table.
 *
 * Usage in routes: ->middleware('can:create_entries').
 */
enum Permission: string
{
    case ViewEntries = 'view_entries';
    case CreateEntries = 'create_entries';
    case EditEntries = 'edit_entries';
    case DeleteEntries = 'delete_entries';
    case ManageProducts = 'manage_products';
    case ManageSuppliers = 'manage_suppliers';
    case ViewReports = 'view_reports';
    case ManageUsers = 'manage_users';
    case ManageUsertypes = 'manage_usertypes';

    public function column(): string
    {
        return 'can_' . $this->value;
    }

    public function label(): string
    {
        return __('permissions.' . $this->value);
    }

    /**
     * @return list<string>
     */
    public static function columns(): array
    {
        return array_map(static fn (self $permission) => $permission->column(), self::cases());
    }
}
