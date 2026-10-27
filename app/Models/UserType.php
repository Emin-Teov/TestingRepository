<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission;
use DateTimeImmutable;
use Framework\Database\Model;

/**
 * A kind of user (Administrator, Storekeeper, ...) and the permissions it grants.
 *
 * @property-read int $id
 * @property string $name
 * @property ?string $description
 * @property bool $is_locked
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class UserType extends Model
{
    protected static string $table = 'usertypes';

    protected static array $fillable = [
        'name', 'description',
        'can_view_entries', 'can_create_entries', 'can_edit_entries', 'can_delete_entries',
        'can_manage_products', 'can_manage_suppliers', 'can_view_reports',
        'can_manage_users', 'can_manage_usertypes',
    ];

    protected static array $casts = [
        'id' => 'int',
        'is_locked' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function allows(Permission $permission): bool
    {
        return $this->is_locked || (bool) $this->getAttribute($permission->column());
    }

    /**
     * @return list<Permission>
     */
    public function permissions(): array
    {
        return array_values(array_filter(Permission::cases(), $this->allows(...)));
    }

    public function usersCount(): int
    {
        return User::query()->where('usertype_id', $this->id)->count();
    }
}
