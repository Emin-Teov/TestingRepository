# Recipes

Step-by-step checklists for common tasks. Work through every step. The usual mistakes are a forgotten
translation in one language or a missing route permission.

## Add a CRUD module (example: `warehouses`)

1. **Migration:** `php console make:migration create_warehouses_table`, then define the columns (see database.md).
2. **Model** `app/Models/Warehouse.php`: `$table`, `$fillable`, `$casts` (int ids, float decimals, bool flags,
   datetime dates), `@property` docblock, and `SEARCHABLE` if it has a search box.
3. **Controller** `app/Controllers/Admin/WarehouseController.php`: copy `SupplierController` (the simplest
   complete example) and adapt the rules, the views and the flash keys.
4. **Routes** in `routes/web.php`, inside the `admin` group, wrapped in a permission group:
   ```php
   $router->group(['middleware' => 'can:manage_warehouses'], function (Router $router): void {
       $router->resource('warehouses', WarehouseController::class);
   });
   ```
5. **Views**: copy `resources/views/admin/suppliers/{index,form}.php` to `admin/warehouses/`.
6. **Sidebar**: add a link to `$links` in `resources/views/layouts/admin.php` (route, match prefix, permission, label, icon).
7. **Permission**, if it needs a new one: see "Add a permission" below.
8. **Translations**, in every `lang/*.php`:
   - `nav.warehouses`;
   - a `warehouses` group (title, new, edit, field labels, empty, created, updated, deleted, delete_confirm);
   - `attributes.<field>` for every validated field.
9. **Seeder** (optional): add sample data to the demo seeder, and keep it idempotent.
10. **Tests**: a feature test covering create, update, delete, a validation failure, and 403 without the permission.
11. Run `php console migrate`, then `php vendor/bin/phpunit`, then open the page in the browser.
12. If the project ships an SQL dump, regenerate it (see project.md).

## Add a permission

1. Migration:
   ```php
   $schema->table('usertypes', function (Blueprint $table): void {
       $table->boolean('can_manage_warehouses')->default(false);
   });
   // down(): $schema->table('usertypes', fn (Blueprint $t) => $t->dropColumn('can_manage_warehouses'));
   ```
2. `App\Enums\Permission`: `case ManageWarehouses = 'manage_warehouses';`
3. `App\Models\UserType::$fillable`: add `'can_manage_warehouses'`.
4. `lang/*.php` → `permissions.manage_warehouses` in every language.
5. Use it: route `->middleware('can:manage_warehouses')` and the view `$user->can(Permission::ManageWarehouses)`.
6. The user types form lists every enum case automatically, and the locked Administrator gets it automatically.
   Other types get it when an admin ticks the box. To grant it to the existing types, do that in the migration's `up()` with an UPDATE.
7. Add a test with a user who has the permission and one who doesn't.

## Add a column to an existing table

1. New migration `--table=products` with `$table->string('barcode', 50)->nullable();` (new columns on
   existing tables should be nullable or have a default).
2. Update the model: `$fillable`, `$casts` and the `@property` docblock.
3. Update the controller rules, the form field, the list column if wanted, `attributes.barcode` in every language, and the tests.

## Add a language

1. Copy `lang/en.php` to `lang/<code>.php` and translate **every** value (keep the keys).
2. Add a 22×22 PNG flag at `public/assets/flags/<code>.png`.
3. `config/app.php` → `'locales' => [..., '<code>' => 'Native name']`.

## Add a setting read from `.env`

1. Add `'my_setting' => env('MY_SETTING', 'default')` to the matching file in `config/`.
2. Document it in `.env.example`.
3. Read it with `config('app.my_setting')`, never with `env()` outside config.

## Add a JSON endpoint

In `routes/api.php`, inside the `api` group (`['api', 'auth', 'can']`), add a route with `->middleware('can:...')`.
Return `json($paginatorOrArray)`. Models serialize themselves and `$hidden` hides columns. Add a test
that checks `->data()` and the 401 response for guests.

## Add a file download or export

Build the content in memory (`fopen('php://temp')` and `fputcsv`). For Excel and UTF-8, start with the BOM `"\u{FEFF}"`.
Write plain numbers without thousands separators. Return a `new Response($content, 200, ['Content-Type' => ..., 'Content-Disposition' => 'attachment; filename="..."'])`.
See `StockEntryController::export`.

## Change look and feel

- Colours: the tokens on `:root` in `public/assets/css/app.css`.
- Logo and favicon: `public/assets/img/logo.png`. App name: `APP_NAME` in `.env`.
- Footer contacts: `config/app.php` → `contact`.
