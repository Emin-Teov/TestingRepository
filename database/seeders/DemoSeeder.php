<?php

declare(strict_types=1);

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\StockEntry;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Models\UserType;
use Framework\Auth\Hash;
use Framework\Database\Seeder;

/**
 * Sample categories, suppliers, products, a month of stock entries and one user per non-admin type,
 * so every screen has something to show. Skipped when products already exist.
 */
final class DemoSeeder extends Seeder
{
    private const CATEGORIES = ['Construction materials', 'Food products', 'Chemicals', 'Packaging', 'Electrical'];

    /** name => [phone, email, address] */
    private const SUPPLIERS = [
        'Baku Steel Company' => ['+994 12 555 10 10', 'sales@bakusteel.example', 'Baku, Sabunchu district'],
        'Azersun Distribution' => ['+994 12 555 20 20', 'orders@azersun.example', 'Baku, Khatai district'],
        'Caspian Chemicals' => ['+994 22 555 30 30', 'info@caspianchem.example', 'Sumgait, Industrial zone'],
        'Ganja Packaging' => ['+994 22 555 40 40', null, 'Ganja, Nizami street 12'],
    ];

    /** sku => [name, category, unit symbol, base price, typical quantity] */
    private const PRODUCTS = [
        'CEM-500' => ['Portland cement M500', 'Construction materials', 't', 145.00, 20],
        'REB-12' => ['Rebar A500C, 12 mm', 'Construction materials', 't', 1180.00, 5],
        'SND-01' => ['Washed sand', 'Construction materials', 'm³', 18.50, 40],
        'FLR-50' => ['Wheat flour, premium', 'Food products', 'kg', 0.85, 2000],
        'SUG-01' => ['Sugar', 'Food products', 'kg', 1.35, 1500],
        'OIL-5L' => ['Sunflower oil, 5 L bottle', 'Food products', 'pcs', 11.20, 120],
        'NAOH-25' => ['Caustic soda, 25 kg bag', 'Chemicals', 'pcs', 32.00, 40],
        'SOLV-01' => ['Industrial solvent', 'Chemicals', 'L', 2.40, 600],
        'BOX-60' => ['Cardboard box 60×40×40', 'Packaging', 'pcs', 1.10, 800],
        'FILM-500' => ['Stretch film, 500 mm', 'Packaging', 'roll', 9.75, 60],
        'CBL-3X25' => ['Copper cable 3×2.5 mm²', 'Electrical', 'm', 1.95, 500],
        'LED-18' => ['LED panel 18 W', 'Electrical', 'pcs', 7.40, 150],
    ];

    /** username => [full name, user type, password] */
    private const USERS = [
        'manager' => ['Warehouse Manager', 'Warehouse manager', 'manager123'],
        'storekeeper' => ['Storekeeper', 'Storekeeper', 'storekeeper123'],
        'accountant' => ['Accountant', 'Accountant', 'accountant123'],
    ];

    public function run(): void
    {
        if (Product::count() > 0) {
            return;
        }

        $categories = [];

        foreach (self::CATEGORIES as $name) {
            $categories[$name] = Category::create(['name' => $name])->id;
        }

        $suppliers = [];

        foreach (self::SUPPLIERS as $name => [$phone, $email, $address]) {
            $suppliers[] = Supplier::create(['name' => $name, 'phone' => $phone, 'email' => $email, 'address' => $address])->id;
        }

        $units = [];

        foreach (Unit::all() as $unit) {
            $units[$unit->symbol] = $unit->id;
        }

        $products = [];

        foreach (self::PRODUCTS as $sku => [$name, $category, $unit, $price, $quantity]) {
            $product = Product::create(['sku' => $sku, 'name' => $name, 'category_id' => $categories[$category], 'unit_id' => $units[$unit]]);
            $products[] = [$product->id, $price, $quantity];
        }

        $users = [User::query()->orderBy('id')->first()?->id];

        foreach (self::USERS as $username => [$fullName, $typeName, $password]) {
            $type = UserType::query()->where('name', $typeName)->first();

            if ($type !== null && !User::query()->where('username', $username)->exists()) {
                $users[] = User::create([
                    'usertype_id' => $type->id,
                    'username' => $username,
                    'full_name' => $fullName,
                    'password' => Hash::make($password),
                    'is_active' => true,
                ])->id;
            }
        }

        // About 40 deliveries spread over the last 30 days. mt_srand keeps the demo data the same on every run.
        mt_srand(2026);

        for ($i = 0; $i < 40; $i++) {
            [$productId, $price, $quantity] = $products[mt_rand(0, count($products) - 1)];
            $daysAgo = mt_rand(0, 29);

            (new StockEntry())->forceFill([
                'product_id' => $productId,
                'supplier_id' => $suppliers[mt_rand(0, count($suppliers) - 1)],
                'user_id' => $users[mt_rand(0, count($users) - 1)],
                'quantity' => round($quantity * mt_rand(50, 150) / 100, $quantity >= 100 ? 0 : 1),
                'unit_price' => round($price * mt_rand(95, 108) / 100, 2),
                'entry_date' => date('Y-m-d', strtotime("-$daysAgo days")),
                'document_no' => sprintf('INV-%s-%03d', date('Ym', strtotime("-$daysAgo days")), $i + 1),
                'created_at' => date('Y-m-d H:i:s', strtotime("-$daysAgo days 10:00")),
            ])->save();
        }

        mt_srand();
    }
}
