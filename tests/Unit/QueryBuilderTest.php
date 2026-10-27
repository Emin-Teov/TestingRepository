<?php

declare(strict_types=1);

namespace Tests\Unit;

use Framework\Database\Connection;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

final class QueryBuilderTest extends TestCase
{
    public function test_values_are_bound_and_identifiers_quoted(): void
    {
        $query = (new Connection(['driver' => 'mysql', 'database' => 'x']))
            ->table('items')
            ->where('value', 'like', "%' OR 1=1 --%")
            ->orWhere('id', 5)
            ->whereNull('updated_at')
            ->latest()
            ->limit(10)
            ->offset(20);

        $this->assertSame(
            'SELECT * FROM `items` WHERE `value` LIKE ? OR `id` = ? AND `updated_at` IS NULL ORDER BY `id` DESC LIMIT 10 OFFSET 20',
            $query->toSql(),
        );
        $this->assertSame(["%' OR 1=1 --%", 5], $query->getBindings());
    }

    public function test_search_groups_columns_and_escapes_wildcards(): void
    {
        $query = (new Connection(['driver' => 'sqlite', 'database' => ':memory:']))
            ->table('items')
            ->search(['value', 'description'], '50%');

        $this->assertStringContainsString('("value" LIKE ? ESCAPE \'\\\' OR "description" LIKE ? ESCAPE \'\\\')', $query->toSql());
        $this->assertSame(['%50\%%', '%50\%%'], $query->getBindings());
    }

    public function test_it_rejects_sql_in_identifiers(): void
    {
        $this->expectException(InvalidArgumentException::class);

        (new Connection(['driver' => 'sqlite', 'database' => ':memory:']))->table('items')->orderBy('id; DROP TABLE users');
    }

    public function test_crud_round_trip_on_sqlite(): void
    {
        $db = new Connection(['driver' => 'sqlite', 'database' => ':memory:']);
        $db->statement('CREATE TABLE items (id INTEGER PRIMARY KEY AUTOINCREMENT, value TEXT)');

        $id = $db->table('items')->insert(['value' => 'first']);
        $db->table('items')->insert(['value' => 'second']);
        $db->table('items')->where('id', $id)->update(['value' => 'changed']);

        $this->assertSame('changed', $db->table('items')->find($id)['value']);
        $this->assertSame(2, $db->table('items')->count());

        $page = $db->table('items')->orderBy('id')->paginate(1, 2);
        $this->assertSame('second', $page->items[0]['value']);
        $this->assertSame(2, $page->lastPage());

        $this->assertSame(1, $db->table('items')->where('value', 'second')->delete());
    }

    public function test_sum_respects_where_clauses(): void
    {
        $db = new Connection(['driver' => 'sqlite', 'database' => ':memory:']);
        $db->statement('CREATE TABLE entries (id INTEGER PRIMARY KEY AUTOINCREMENT, product INTEGER, total NUMERIC)');

        foreach ([[1, 10.5], [1, 4.25], [2, 100]] as [$product, $total]) {
            $db->table('entries')->insert(['product' => $product, 'total' => $total]);
        }

        $this->assertSame(14.75, $db->table('entries')->where('product', 1)->sum('total'));
        $this->assertSame(0.0, $db->table('entries')->where('product', 3)->sum('total'));
    }
}
