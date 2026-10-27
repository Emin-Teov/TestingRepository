<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use Framework\Database\Model;

/**
 * @property-read int $id
 * @property string $name
 * @property ?string $phone
 * @property ?string $email
 * @property ?string $address
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class Supplier extends Model
{
    protected static string $table = 'suppliers';

    protected static array $fillable = ['name', 'phone', 'email', 'address'];

    protected static array $casts = [
        'id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const SEARCHABLE = ['name', 'phone', 'email', 'address'];
}
