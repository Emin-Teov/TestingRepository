<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use Framework\Database\Model;

/**
 * A unit of measurement: ton, kilogram, piece, ...
 *
 * @property-read int $id
 * @property string $name
 * @property string $symbol
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class Unit extends Model
{
    protected static string $table = 'units';

    protected static array $fillable = ['name', 'symbol'];

    protected static array $casts = [
        'id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function isUsed(): bool
    {
        return Product::query()->where('unit_id', $this->id)->exists();
    }
}
