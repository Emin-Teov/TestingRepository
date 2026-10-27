<?php

declare(strict_types=1);

namespace App\Support;

/**
 * Number formatting for views and exports: "1 250.5 kg", "3 400.00 AZN".
 */
final class Format
{
    /**
     * Up to three decimals, without trailing zeros.
     */
    public static function quantity(float $value, ?string $unit = null): string
    {
        $formatted = rtrim(rtrim(number_format($value, 3, '.', ' '), '0'), '.');

        return $unit !== null ? "$formatted $unit" : $formatted;
    }

    public static function money(float $value, bool $withCurrency = true): string
    {
        $formatted = number_format($value, 2, '.', ' ');

        return $withCurrency ? $formatted . ' ' . config('app.currency') : $formatted;
    }
}
