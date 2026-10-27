<?php

declare(strict_types=1);

namespace Framework\Support;

final class Str
{
    public static function snake(string $value): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '_$0', $value));
    }

    public static function studly(string $value): string
    {
        return str_replace(' ', '', ucwords(str_replace(['-', '_'], ' ', $value)));
    }

    public static function limit(string $value, int $limit = 100, string $end = '…'): string
    {
        return mb_strlen($value) <= $limit ? $value : rtrim(mb_substr($value, 0, $limit)) . $end;
    }
}
