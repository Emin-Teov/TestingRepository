<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use Framework\Database\Model;

/**
 * @property-read int $id
 * @property string $sku
 * @property string $name
 * @property ?int $category_id
 * @property int $unit_id
 * @property ?string $description
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class Product extends Model
{
    protected static string $table = 'products';

    protected static array $fillable = ['sku', 'name', 'category_id', 'unit_id', 'description'];

    protected static array $casts = [
        'id' => 'int',
        'category_id' => 'int',
        'unit_id' => 'int',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public const SEARCHABLE = ['sku', 'name', 'description'];

    public function hasEntries(): bool
    {
        return StockEntry::query()->where('product_id', $this->id)->exists();
    }

    /**
     * Received quantity and value per product, for the stock report and the API.
     * Products without entries in the period are included with zeros.
     *
     * @return list<array{id: int, sku: string, name: string, category: ?string, unit: string,
     *                    entries: int, quantity: float, value: float, average_price: float, last_entry: ?string}>
     */
    public static function stockSummary(?int $categoryId = null, ?string $from = null, ?string $to = null): array
    {
        $join = '';
        $bindings = [];

        // Date filters belong in the JOIN, not the WHERE, so products without entries still appear.
        if ($from !== null) {
            $join .= ' AND e.entry_date >= ?';
            $bindings[] = $from;
        }

        if ($to !== null) {
            $join .= ' AND e.entry_date <= ?';
            $bindings[] = $to;
        }

        $where = '';

        if ($categoryId !== null) {
            $where = ' WHERE p.category_id = ?';
            $bindings[] = $categoryId;
        }

        $rows = static::connection()->select(
            'SELECT p.id, p.sku, p.name, c.name AS category, u.symbol AS unit,
                    COUNT(e.id) AS entries, COALESCE(SUM(e.quantity), 0) AS quantity,
                    COALESCE(SUM(e.total_value), 0) AS value, MAX(e.entry_date) AS last_entry
             FROM products p
             JOIN units u ON u.id = p.unit_id
             LEFT JOIN categories c ON c.id = p.category_id
             LEFT JOIN stock_entries e ON e.product_id = p.id' . $join . $where . '
             GROUP BY p.id, p.sku, p.name, c.name, u.symbol
             ORDER BY p.name',
            $bindings,
        );

        return array_map(static function (array $row): array {
            $quantity = (float) $row['quantity'];
            $value = (float) $row['value'];

            return [
                'id' => (int) $row['id'],
                'sku' => $row['sku'],
                'name' => $row['name'],
                'category' => $row['category'],
                'unit' => $row['unit'],
                'entries' => (int) $row['entries'],
                'quantity' => $quantity,
                'value' => $value,
                'average_price' => $quantity > 0 ? round($value / $quantity, 2) : 0.0,
                'last_entry' => $row['last_entry'] !== null ? substr((string) $row['last_entry'], 0, 10) : null,
            ];
        }, $rows);
    }
}
