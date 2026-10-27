<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Enums\Permission;
use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserType;
use Framework\Http\JsonResponse;
use Framework\Http\RedirectResponse;
use Tests\TestCase;

final class HttpTest extends TestCase
{
    private function createProduct(string $sku = 'CEM-500', string $unit = 't'): Product
    {
        $unitId = Unit::query()->where('symbol', $unit)->first()?->id ?? Unit::create(['name' => "Unit $unit", 'symbol' => $unit])->id;

        return Product::create(['sku' => $sku, 'name' => "Product $sku", 'unit_id' => $unitId]);
    }

    private function createEntry(Product $product, string $quantity = '2.5', string $price = '100', string $date = '2026-10-01'): StockEntry
    {
        $entry = (new StockEntry())->fill(['product_id' => $product->id, 'quantity' => $quantity, 'unit_price' => $price, 'entry_date' => $date]);
        $entry->save();

        return $entry;
    }

    public function test_home_redirects_guests_to_login(): void
    {
        $this->assertSame('/admin', $this->call('GET', '/')->url());

        $response = $this->call('GET', '/admin');

        $this->assertInstanceOf(RedirectResponse::class, $response);
        $this->assertSame('/login', $response->url());
    }

    public function test_login_with_valid_and_invalid_credentials(): void
    {
        $this->createUser(password: 'password123');

        $failed = $this->call('POST', '/login', ['username' => 'admin', 'password' => 'wrong']);
        $this->assertSame(302, $failed->status());
        $this->assertArrayNotHasKey('auth_id', $_SESSION);

        $response = $this->call('POST', '/login', ['username' => 'admin', 'password' => 'password123']);
        $this->assertSame('/admin', $response->url());
        $this->assertArrayHasKey('auth_id', $_SESSION);
    }

    public function test_disabled_users_cannot_sign_in_or_keep_using_a_session(): void
    {
        $user = $this->createUser([Permission::ViewEntries], 'disabled', active: false);

        $this->call('POST', '/login', ['username' => 'disabled', 'password' => 'password123']);
        $this->assertArrayNotHasKey('auth_id', $_SESSION);

        $this->actingAs($user);
        $this->assertSame('/login', $this->call('GET', '/admin')->url());
    }

    public function test_post_without_valid_csrf_token_is_rejected(): void
    {
        $response = $this->call('POST', '/login', ['username' => 'a', 'password' => 'b', '_token' => 'forged']);

        $this->assertSame(419, $response->status());
    }

    public function test_storing_an_entry_calculates_the_total_and_records_the_user(): void
    {
        $user = $this->createUser([Permission::ViewEntries, Permission::CreateEntries], 'storekeeper');
        $this->actingAs($user);
        $product = $this->createProduct();
        $supplier = Supplier::create(['name' => 'Baku Steel']);

        $response = $this->call('POST', '/admin/entries', [
            'product_id' => $product->id,
            'supplier_id' => $supplier->id,
            'quantity' => '12.5',
            'unit_price' => '145.20',
            'entry_date' => '2026-10-27',
            'document_no' => 'INV-1',
            'note' => '',
        ]);

        $this->assertSame('/admin/entries', $response->url());
        $entry = StockEntry::query()->first();
        $this->assertSame(12.5, $entry->quantity);
        $this->assertSame(1815.0, $entry->total_value);
        $this->assertSame('2026-10-27', $entry->entry_date->format('Y-m-d'));
        $this->assertSame($user->id, $entry->user_id);
        $this->assertNull($entry->note);

        $page = $this->call('GET', '/admin/entries')->content();
        $this->assertStringContainsString('12.5 t', $page);
        $this->assertStringContainsString('1 815.00 AZN', $page);
    }

    public function test_invalid_entry_flashes_errors_and_old_input(): void
    {
        $this->actingAs($this->createUser());

        $response = $this->call('POST', '/admin/entries', ['product_id' => 999, 'quantity' => '0', 'unit_price' => 'abc', 'entry_date' => '2026-02-30']);

        $this->assertSame(302, $response->status());
        $this->assertSame(['product_id', 'quantity', 'unit_price', 'entry_date'], array_keys($_SESSION['_flash']['_errors']));
        $this->assertSame('abc', $_SESSION['_flash']['_old']['unit_price']);
        $this->assertSame(0, StockEntry::count());
    }

    public function test_updating_an_entry_recalculates_the_total(): void
    {
        $this->actingAs($this->createUser());
        $entry = $this->createEntry($product = $this->createProduct());

        $this->call('PUT', '/admin/entries/' . $entry->id, [
            'product_id' => $product->id, 'quantity' => '4', 'unit_price' => '0.25', 'entry_date' => '2026-10-02',
        ]);

        $this->assertSame(1.0, StockEntry::find($entry->id)->total_value);
    }

    public function test_permissions_of_the_user_type_decide_what_is_allowed(): void
    {
        $entry = $this->createEntry($this->createProduct());
        $this->actingAs($this->createUser([Permission::ViewEntries, Permission::CreateEntries], 'storekeeper'));

        $this->assertSame(200, $this->call('GET', '/admin')->status());
        $this->assertSame(200, $this->call('GET', '/admin/entries')->status());
        $this->assertSame(200, $this->call('GET', '/admin/entries/create')->status());
        $this->assertSame(403, $this->call('GET', '/admin/entries/' . $entry->id . '/edit')->status());
        $this->assertSame(403, $this->call('DELETE', '/admin/entries/' . $entry->id)->status());
        $this->assertSame(403, $this->call('GET', '/admin/products')->status());
        $this->assertSame(403, $this->call('GET', '/admin/reports/stock')->status());
        $this->assertSame(403, $this->call('GET', '/admin/users')->status());
        $this->assertSame(403, $this->call('GET', '/admin/usertypes')->status());
        $this->assertNotNull(StockEntry::find($entry->id));

        // The sidebar only links to what the user may open.
        $page = $this->call('GET', '/admin')->content();
        $this->assertStringContainsString('/admin/entries', $page);
        $this->assertStringNotContainsString('/admin/users', $page);
    }

    public function test_user_type_permissions_are_saved_from_checkboxes(): void
    {
        $this->actingAs($this->createUser());

        $this->call('POST', '/admin/usertypes', ['name' => 'Auditor', 'permissions' => ['view_entries', 'view_reports', 'not_a_permission']]);

        $type = UserType::query()->where('name', 'Auditor')->first();
        $this->assertSame([Permission::ViewEntries, Permission::ViewReports], $type->permissions());
    }

    public function test_user_types_in_use_or_built_in_cannot_be_deleted(): void
    {
        $admin = $this->createUser();
        $this->actingAs($admin);
        $storekeeper = $this->createUser([Permission::ViewEntries], 'storekeeper');

        $this->call('DELETE', '/admin/usertypes/' . $admin->usertype_id);
        $this->call('DELETE', '/admin/usertypes/' . $storekeeper->usertype_id);

        $this->assertSame(2, UserType::count());
    }

    public function test_admin_cannot_delete_disable_or_demote_themselves(): void
    {
        $admin = $this->createUser();
        $this->actingAs($admin);
        $viewer = $this->createUser([Permission::ViewEntries], 'viewer');

        $this->call('DELETE', '/admin/users/' . $admin->id);
        $this->call('PUT', '/admin/users/' . $admin->id, ['username' => 'admin', 'usertype_id' => $admin->usertype_id]);
        $this->call('PUT', '/admin/users/' . $admin->id, ['username' => 'admin', 'usertype_id' => $viewer->usertype_id, 'is_active' => '1']);

        $fresh = User::find($admin->id);
        $this->assertNotNull($fresh);
        $this->assertTrue($fresh->is_active);
        $this->assertSame($admin->usertype_id, $fresh->usertype_id);
    }

    public function test_products_with_entries_keep_their_unit_and_cannot_be_deleted(): void
    {
        $this->actingAs($this->createUser());
        $product = $this->createProduct();
        $this->createEntry($product);
        $kg = Unit::create(['name' => 'Kilogram', 'symbol' => 'kg']);

        $this->call('PUT', '/admin/products/' . $product->id, ['sku' => $product->sku, 'name' => 'Cement', 'unit_id' => $kg->id]);
        $this->assertArrayHasKey('unit_id', $_SESSION['_flash']['_errors']);

        $this->call('DELETE', '/admin/products/' . $product->id);
        $this->assertNotNull(Product::find($product->id));

        $this->call('DELETE', '/admin/units/' . $product->unit_id);
        $this->assertNotNull(Unit::find($product->unit_id));
    }

    public function test_deleting_a_category_keeps_its_products(): void
    {
        $this->actingAs($this->createUser());
        $category = Category::create(['name' => 'Cement']);
        $product = $this->createProduct();
        $product->update(['category_id' => $category->id]);

        $this->call('DELETE', '/admin/categories/' . $category->id);

        $this->assertNull(Product::find($product->id)->category_id);
    }

    public function test_entries_can_be_filtered_and_exported(): void
    {
        $this->actingAs($this->createUser());
        $cement = $this->createProduct('CEM');
        $this->createEntry($cement, '1', '10', '2026-09-15');
        $this->createEntry($cement, '2', '10', '2026-10-15');
        $this->createEntry($this->createProduct('FLR', 'kg'), '3', '10', '2026-10-16');

        $this->assertSame(1, StockEntry::filtered(\Framework\Http\Request::create('GET', '/', ['product_id' => $cement->id, 'from' => '2026-10-01']))->count());

        $csv = $this->call('GET', '/admin/entries/export', ['product_id' => $cement->id]);
        $this->assertSame('text/csv; charset=utf-8', $csv->headers()['Content-Type']);
        $this->assertSame(3, substr_count($csv->content(), "\n"));
        $this->assertStringContainsString('2026-10-15,CEM,"Product CEM",,2,t,10.00,20.00', $csv->content());
    }

    public function test_stock_report_sums_per_product_within_the_period(): void
    {
        $this->actingAs($this->createUser());
        $cement = $this->createProduct('CEM');
        $this->createProduct('EMPTY');
        $this->createEntry($cement, '1', '10', '2026-09-15');
        $this->createEntry($cement, '2', '40', '2026-10-15');

        $rows = array_column(Product::stockSummary(from: '2026-10-01'), null, 'sku');

        $this->assertSame(2.0, $rows['CEM']['quantity']);
        $this->assertSame(80.0, $rows['CEM']['value']);
        $this->assertSame(40.0, $rows['CEM']['average_price']);
        $this->assertSame(0, $rows['EMPTY']['entries']);
        $this->assertSame(200, $this->call('GET', '/admin/reports/stock', ['from' => '2026-10-01'])->status());
    }

    public function test_api_requires_login_and_returns_paginated_entries(): void
    {
        $this->assertSame(401, $this->call('GET', '/api/entries')->status());

        $this->actingAs($this->createUser());
        $product = $this->createProduct();

        foreach (range(1, 3) as $day) {
            $this->createEntry($product, '1', '5', "2026-10-0$day");
        }

        $response = $this->call('GET', '/api/entries', ['per_page' => 2]);

        $this->assertInstanceOf(JsonResponse::class, $response);
        $this->assertSame(['current_page' => 1, 'last_page' => 2, 'per_page' => 2, 'total' => 3], $response->data()['meta']);
        $this->assertSame('2026-10-03T00:00:00+04:00', $response->data()['data'][0]['entry_date']);
        $this->assertEquals(15, $this->call('GET', '/api/stock')->data()['data'][0]['value']);
        $this->assertSame(404, $this->call('GET', '/api/entries/999')->status());
    }
}
