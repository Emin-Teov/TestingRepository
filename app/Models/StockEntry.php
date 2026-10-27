<?php

declare(strict_types=1);

namespace App\Models;

use DateTimeImmutable;
use Framework\Database\Model;
use Framework\Database\ModelQuery;
use Framework\Http\Request;
use Framework\Validation\Validator;

/**
 * Goods received into the warehouse.
 *
 * @property-read int $id
 * @property int $product_id
 * @property ?int $supplier_id
 * @property ?int $user_id
 * @property float $quantity
 * @property float $unit_price
 * @property-read float $total_value Always quantity × unit_price, calculated on save.
 * @property DateTimeImmutable $entry_date
 * @property ?string $document_no
 * @property ?string $note
 * @property ?DateTimeImmutable $created_at
 * @property ?DateTimeImmutable $updated_at
 */
final class StockEntry extends Model
{
    protected static string $table = 'stock_entries';

    protected static array $fillable = ['product_id', 'supplier_id', 'quantity', 'unit_price', 'entry_date', 'document_no', 'note'];

    protected static array $casts = [
        'id' => 'int',
        'product_id' => 'int',
        'supplier_id' => 'int',
        'user_id' => 'int',
        'quantity' => 'float',
        'unit_price' => 'float',
        'total_value' => 'float',
        'entry_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function save(): bool
    {
        // Store plain decimal strings ("1500.000"), never floats in exponent form.
        $quantity = round((float) $this->getAttribute('quantity'), 3);
        $price = round((float) $this->getAttribute('unit_price'), 2);

        $this->setAttribute('quantity', number_format($quantity, 3, '.', ''));
        $this->setAttribute('unit_price', number_format($price, 2, '.', ''));
        $this->setAttribute('total_value', number_format(round($quantity * $price, 2), 2, '.', ''));
        $this->setAttribute('entry_date', substr((string) ($this->attributes['entry_date'] ?? date('Y-m-d')), 0, 10));

        return parent::save();
    }

    /**
     * Entries matching the filters of the entries page: q, product_id, supplier_id, from, to.
     *
     * @return ModelQuery<StockEntry>
     */
    public static function filtered(Request $request): ModelQuery
    {
        $query = static::query()->search(['document_no', 'note'], $request->string('q'));

        if ($productId = $request->integer('product_id')) {
            $query->where('product_id', $productId);
        }

        if ($supplierId = $request->integer('supplier_id')) {
            $query->where('supplier_id', $supplierId);
        }

        foreach (['from' => '>=', 'to' => '<='] as $key => $operator) {
            $date = $request->string($key);

            if (Validator::isDate($date)) {
                $query->where('entry_date', $operator, $date);
            }
        }

        return $query;
    }

    /**
     * Load everything the entry tables display with one query per table.
     *
     * @param list<StockEntry> $entries
     * @return array{products: array<int, Product>, units: array<int, Unit>, suppliers: array<int, Supplier>, users: array<int, User>}
     */
    public static function relationsFor(array $entries): array
    {
        $products = Product::findMany(array_map(static fn (self $entry) => $entry->product_id, $entries));

        return [
            'products' => $products,
            'units' => Unit::findMany(array_map(static fn (Product $product) => $product->unit_id, $products)),
            'suppliers' => Supplier::findMany(array_map(static fn (self $entry) => $entry->supplier_id, $entries)),
            'users' => User::findMany(array_map(static fn (self $entry) => $entry->user_id, $entries)),
        ];
    }

    /**
     * Total received quantity per product.
     *
     * @param list<int> $productIds
     * @return array<int, float> product id => quantity
     */
    public static function quantitiesFor(array $productIds): array
    {
        if ($productIds === []) {
            return [];
        }

        $placeholders = implode(', ', array_fill(0, count($productIds), '?'));
        $rows = static::connection()->select(
            "SELECT product_id, SUM(quantity) AS quantity FROM stock_entries WHERE product_id IN ($placeholders) GROUP BY product_id",
            array_values($productIds),
        );

        return array_column(array_map(static fn (array $row) => [(int) $row['product_id'], (float) $row['quantity']], $rows), 1, 0);
    }
}
