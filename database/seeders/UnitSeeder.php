<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Unit;
use Framework\Database\Seeder;

final class UnitSeeder extends Seeder
{
    private const UNITS = [
        ['Ton', 't'],
        ['Kilogram', 'kg'],
        ['Gram', 'g'],
        ['Piece', 'pcs'],
        ['Litre', 'L'],
        ['Metre', 'm'],
        ['Square metre', 'm²'],
        ['Cubic metre', 'm³'],
        ['Box', 'box'],
        ['Pack', 'pack'],
        ['Roll', 'roll'],
        ['Pallet', 'plt'],
    ];

    public function run(): void
    {
        if (Unit::count() > 0) {
            return;
        }

        foreach (self::UNITS as [$name, $symbol]) {
            Unit::create(['name' => $name, 'symbol' => $symbol]);
        }
    }
}
