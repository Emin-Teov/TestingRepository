<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\Permission;
use DateTimeImmutable;
use Framework\Database\Model;

/**
 * @property-read int $id
 * @property int $usertype_id
 * @property string $username
 * @property ?string $full_name
 * @property string $password
 * @property bool $is_active
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class User extends Model
{
    protected static string $table = 'users';

    protected static array $fillable = ['usertype_id', 'username', 'full_name', 'password', 'is_active'];

    protected static array $hidden = ['password'];

    protected static array $casts = [
        'id' => 'int',
        'usertype_id' => 'int',
        'is_active' => 'bool',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    private ?UserType $userType = null;

    public function userType(): UserType
    {
        if ($this->userType?->id !== $this->usertype_id) {
            $this->userType = UserType::findOrFail($this->usertype_id);
        }

        return $this->userType;
    }

    /**
     * Inactive users have no permissions at all.
     */
    public function can(Permission ...$permissions): bool
    {
        if (!$this->is_active) {
            return false;
        }

        foreach ($permissions as $permission) {
            if (!$this->userType()->allows($permission)) {
                return false;
            }
        }

        return true;
    }

    public function displayName(): string
    {
        return $this->full_name ?: $this->username;
    }
}
