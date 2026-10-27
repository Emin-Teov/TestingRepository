<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use Framework\Database\Model;

/**
 * @property-read int $id
 * @property string $name
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class Category extends Model
{
    protected static string $table = 'categories';

    protected static array $fillable = ['name'];

    protected static array $casts = [
        'id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
