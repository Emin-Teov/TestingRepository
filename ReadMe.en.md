[Azərbaycanca](ReadMe.md) | **English**

# Warehouse

A warehouse management application built on a small MVC framework for **PHP 8.2+** and **MySQL**.
It records the goods entering the warehouse: product, unit of measurement (t, kg, pcs, L, …),
quantity, unit price, total value (quantity × price), entry date, supplier and document number.
The interface is in Azerbaijani (default), English and Russian. Users switch languages with the flags in the top bar.

The framework has no runtime dependencies. Composer is only needed to run the tests.

## Quick start (XAMPP)

1. Put the project in `htdocs/Framework` and start Apache and MySQL.
2. Import `Session.sql` in phpMyAdmin (or run `mysql -u root < Session.sql`). It creates the `warehouse`
   database with every table, the foreign keys and sample data.
3. `cp .env.example .env`. It already points to `warehouse` with user `root` and an empty password.
4. Open `http://localhost/Framework/` and sign in:

| Username      | Password         | User type         |
|---------------|------------------|-------------------|
| `admin`       | `admin12345`     | Administrator     |
| `manager`     | `manager123`     | Warehouse manager |
| `storekeeper` | `storekeeper123` | Storekeeper       |
| `accountant`  | `accountant123`  | Accountant        |

Change these passwords after the first login.

To build the database from the migrations instead of the SQL file, run `php console migrate:fresh --seed`.
The admin password then comes from `ADMIN_PASSWORD` in `.env`; if it is empty, a random one is generated and printed.
More users: `php console user:create <username> <password> --type="Storekeeper"`.

On hosting without SSH, upload the project, create `.env` and import `Session.sql`. When later updates
add migrations, get their SQL on your own computer with `php console migrate --pretend` and run it in phpMyAdmin.

## Database

| Table           | Contents                                                                                    |
|-----------------|---------------------------------------------------------------------------------------------|
| `usertypes`     | User types (Administrator, Storekeeper, …). Every permission is a `can_*` boolean column.     |
| `users`         | Accounts. `usertype_id` → `usertypes.id` (foreign key; a type that has users cannot be deleted). |
| `units`         | Units of measurement: name and symbol.                                                      |
| `categories`    | Product categories.                                                                         |
| `suppliers`     | Suppliers with phone, email and address.                                                    |
| `products`      | SKU, name, `category_id` → `categories`, `unit_id` → `units`.                               |
| `stock_entries` | Goods received: `product_id`, `supplier_id`, `user_id`, `quantity`, `unit_price`, `total_value`, `entry_date`, `document_no`, `note`. |

`StockEntry::save()` always sets `total_value` to `quantity × unit_price`, so the two can never disagree.
A product's unit cannot change once the product has entries. Products, units and user types that are still in use cannot be deleted.

## Permissions

Every user has one user type, and the type's `can_*` columns decide what the user may do. Change them
with the checkboxes under **User types**, or directly in the `usertypes` table.

| Permission (`can_…`) | Allows                                    |
|----------------------|-------------------------------------------|
| `view_entries`       | Stock entries list and CSV export         |
| `create_entries`     | Receive goods (add entries)               |
| `edit_entries`       | Edit entries                              |
| `delete_entries`     | Delete entries                            |
| `manage_products`    | Products, categories and units            |
| `manage_suppliers`   | Suppliers                                 |
| `view_reports`       | Stock report                              |
| `manage_users`       | Users                                     |
| `manage_usertypes`   | User types and their permissions          |

The built-in Administrator type (`is_locked = 1`) always has every permission and cannot be deleted.
Disabled users (`is_active = 0`) cannot sign in. Nobody can delete or disable their own account, or remove
their own access to users and user types. In routes, permissions are checked with the `can` middleware:
`->middleware('can:create_entries')`.

## Screens

- **Dashboard**: product count, total value received, entries and value this month, and a 7-day chart.
- **Stock entries**: filter by product, supplier, date range and document number. Shows the total of the listed entries and exports them to CSV (opens in Excel).
- **Products, Categories, Units, Suppliers**: master data. The product list shows how much of each product has been received.
- **Stock report**: quantity, average price and value per product for a period or category. Printable.
- **Users, User types**: administration.

## For developers: customizing the application

Everything below can be changed without touching `framework/`. Step-by-step guides for bigger changes,
also written for AI assistants, are in [.ai-knowledge/](.ai-knowledge/README.md). Start with
[recipes.md](.ai-knowledge/recipes.md).

### Settings (`.env`)

| Key | Default | What it changes |
|-----|---------|-----------------|
| `APP_NAME` | `Warehouse` | Name in the sidebar, page titles and footer |
| `APP_ENV` / `APP_DEBUG` | `local` / `true` | Use `production` / `false` on a live server: hides error details and protects destructive commands |
| `APP_URL` | *(empty)* | Leave empty; set it only if links point to the wrong folder |
| `APP_TIMEZONE` | `Asia/Baku` | Dates and "this month" on the dashboard |
| `APP_LOCALE` | `az` | Default language (`az`, `en`, `ru`); users can switch with the flags |
| `APP_CURRENCY` | `AZN` | Currency shown next to every price and value |
| `DB_*` | `warehouse` on `127.0.0.1`, user `root`, empty password | Database connection |
| `SESSION_NAME` | `warehouse_session` | Cookie name. Give each project on the same domain its own name. |
| `ADMIN_USERNAME` / `ADMIN_PASSWORD` | `admin` / — | First administrator, created by `php console db:seed` |

`.env` is not committed. When you add a setting, also add it to `.env.example` and read it in a `config/*.php` file.

### Look and branding

| What | Where |
|------|-------|
| Colours (dark theme) | CSS variables at the top of [public/assets/css/app.css](public/assets/css/app.css) (`--bg`, `--surface`, `--accent`, …) |
| Admin layout, tables, forms | [public/assets/css/admin.css](public/assets/css/admin.css) |
| Logo and favicon | `public/assets/img/logo.png` |
| Sidebar menu (order, icons, who sees what) | the `$links` array at the top of [resources/views/layouts/admin.php](resources/views/layouts/admin.php) |
| Footer contacts | `contact` in [config/app.php](config/app.php) |
| Number formatting (decimals, thousands separator) | [app/Support/Format.php](app/Support/Format.php) |
| Rows per page | the `paginate(20, …)` call in each controller's `index()` |

### Texts and languages

- Every label and message is in [lang/en.php](lang/en.php), [lang/az.php](lang/az.php) and [lang/ru.php](lang/ru.php).
  Change the wording there. Keep the keys identical in all three files.
- New language: copy `lang/en.php` to `lang/<code>.php`, add a flag at `public/assets/flags/<code>.png`, and add
  the code to `locales` in `config/app.php`.
- Remove a language: delete its line from `locales` in `config/app.php` (its flag disappears from the top bar).
- Untranslated keys fall back to English (`fallback_locale`), so keep `lang/en.php` complete.

### Business data

- **Units, categories, suppliers, user types**: change them in the application. No code is needed.
- **Initial units and user types** for new installations: [database/seeders/UnitSeeder.php](database/seeders/UnitSeeder.php)
  and [UserTypeSeeder.php](database/seeders/UserTypeSeeder.php).
- **Demo data**: [DemoSeeder.php](database/seeders/DemoSeeder.php). Remove it from `DatabaseSeeder` for a clean installation.
- **Validation limits** (max quantity, text lengths, …): the `RULES` constant or `rules()` method in each controller.

### Permissions

To add a new permission, add a `can_…` column to `usertypes` with a migration and a case to
[app/Enums/Permission.php](app/Enums/Permission.php). Then add it to `UserType::$fillable`, translate it under
`permissions` in every language, and protect the routes with `->middleware('can:<name>')`. The user types screen
shows the new checkbox automatically. Full checklist: [.ai-knowledge/recipes.md](.ai-knowledge/recipes.md#add-a-permission).

### New screens and tables

Follow "Add a CRUD module" in [.ai-knowledge/recipes.md](.ai-knowledge/recipes.md). In short: a migration,
a model, a controller (copy `SupplierController`), routes with a permission, two views (copy `admin/suppliers`),
a sidebar link, translations and a test. Never change a migration that has already run; add a new one.

### Rules worth keeping

- Escape output with `e()`, validate input with `$request->validate()`, and give every form `csrf_field()`.
- Check permissions on the **route**. Hiding a button is not enough.
- Run `php vendor/bin/phpunit` before committing. Tests use an in-memory SQLite database, so they never touch MySQL.
- After a schema change, regenerate `Session.sql` ([.ai-knowledge/project.md](.ai-knowledge/project.md) explains how).

### Before going live

1. Set `APP_ENV=production` and `APP_DEBUG=false`, and use a database user with a strong password.
2. Change the demo passwords, or delete the demo users (`manager`, `storekeeper`, `accountant`).
3. Make sure `storage/` is writable by the web server.
4. Serve the site over HTTPS. The session cookie becomes `Secure` automatically.

### Forgotten passwords

Passwords are stored as one-way bcrypt hashes, so nobody can read them back, not even from the database.
To reset one, use **Users → Edit → Password**. If no administrator can sign in, create a new admin user:

```bash
php console user:create newadmin "a-strong-password" --type="Administrator"
```

## Deploying to a server

The application needs **PHP 8.2+** (extensions `pdo_mysql` and `mbstring`), **MySQL 5.7+ or MariaDB 10.3+**,
and Apache with `mod_rewrite` (or nginx with the web root set to `public/`).

**Shared hosting (cPanel / DirectAdmin / free hosts, no SSH):**

1. Create a MySQL database and user in the control panel.
2. Edit the top of `Session.sql`: replace `warehouse` in `CREATE DATABASE` / `USE` with your database name,
   or delete those two lines and select the database in phpMyAdmin. Then import the file in phpMyAdmin.
3. Upload all project files (not `.git`, `tests`, `vendor`) into the site folder, e.g. `htdocs/` or `public_html/`.
   The root `.htaccess` sends every request to `public/` and blocks `.env`.
4. Create `.env` on the server from `.env.example` with the hosting database details, `APP_ENV=production`
   and `APP_DEBUG=false`.
5. Make `storage/` writable, open the site, sign in, and change the demo passwords.

**VPS / cloud server (Ubuntu):** install `apache2 php8.3 php8.3-mysql php8.3-mbstring mariadb-server`,
point the virtual host's `DocumentRoot` to `/var/www/warehouse/public`, enable `mod_rewrite`,
import `Session.sql`, create `.env`, run `chown -R www-data storage`, and enable HTTPS with
`certbot --apache`.

## Console

```
php console                       list all commands
php console serve                 development server  [--host=] [--port=]
php console migrate               run pending migrations  [--seed] [--pretend]
php console migrate:status        which migrations have run
php console migrate:rollback      undo the last batch  [--step=N]
php console migrate:reset         undo every migration
php console migrate:fresh         drop all tables and migrate again  [--seed]
php console db:seed               run seeders  [--class=DatabaseSeeder]
php console make:migration name   new migration  [--create=table] [--table=table]
php console make:model Name       new model  [--migration]
php console make:controller Name  new controller (e.g. Admin/PostController)
php console make:seeder Name      new seeder
php console route:list            all routes with names and middleware
php console user:create           new user  <username> <password> [--type="User type"]
```

Destructive commands (rollback, reset, fresh) refuse to run when `APP_ENV=production` unless you pass `--force`.

## JSON API

The API uses the dashboard login. Guests get 401, and users without the permission get 403.

| Endpoint                | Permission     | Description                                    |
|-------------------------|----------------|------------------------------------------------|
| `GET /api/stock`        | `view_reports` | Quantity and value received per product        |
| `GET /api/entries`      | `view_entries` | Paginated entries: `?page=`, `?per_page=` (max 100), `?product_id=`, `?supplier_id=`, `?from=`, `?to=`, `?q=` |
| `GET /api/entries/{id}` | `view_entries` | One entry, or 404                              |

## Project layout

```
app/                Your application: controllers, models, enums, middleware, console commands
bootstrap/          Creates the application and registers services in the container
config/             app, auth, database and middleware settings (values come from .env)
database/
  migrations/       Versioned schema changes
  seeders/          Initial and demo data
framework/          The framework itself (namespace Framework\)
lang/               en.php, az.php, ru.php
public/             Web root: index.php and assets
resources/views/    Plain-PHP templates with layouts, sections and partials
routes/             web.php and api.php
storage/            Logs, cache and the SQLite database (not committed)
tests/              PHPUnit tests (composer install && composer test)
.ai-knowledge/      Framework knowledge base for AI assistants
```

## Writing code

**Routes** (`routes/web.php`):

```php
$router->get('/posts/{id:\d+}', [PostController::class, 'show'])->name('posts.show');
$router->group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'can:manage_posts']], function (Router $router) {
    $router->resource('posts', Admin\PostController::class);   // index, create, store, edit, update, destroy
});
```

**Controllers** receive route parameters and services through their method signatures:

```php
public function update(int $id, Request $request): RedirectResponse
{
    $data = $request->validate(['title' => 'required|string|max:100']);
    Post::findOrFail($id)->update($data);

    return to_route('admin.posts.index')->with('success', __('posts.updated'));
}
```

**Migrations** (`php console make:migration create_posts_table`):

```php
$schema->create('posts', function (Blueprint $table): void {
    $table->id();
    $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
    $table->string('title', 100)->unique();
    $table->text('body');
    $table->boolean('published')->default(false);
    $table->timestamps();
});
```

**Queries** always use prepared statements:

```php
Post::query()->where('published', true)->search(['title', 'body'], $term)->latest()->paginate(15, $page);
app(Connection::class)->table('posts')->where('id', 5)->update(['title' => 'New']);
```

**Views** are escaped with `e()`:

```php
<?php $this->extend('layouts/app', ['title' => __('posts.title')]) ?>
<h1><?= e($post->title) ?></h1>
```

Validation rules: `required, nullable, string, integer, numeric, boolean, email, min, max, in,
alpha_dash, confirmed, date (Y-m-d), unique:table,column[,ignoreId], exists:table,column`.
Empty optional fields are returned as `null`.

## Security notes

- Passwords are stored with `password_hash()`. Login is throttled after 5 failed attempts.
- Every query uses bound parameters. Table and column names are checked against a strict pattern.
- Each session has one CSRF token, checked on every POST, PUT, PATCH and DELETE request.
  The session id changes after login.
- Output is escaped in templates. Responses send `X-Frame-Options`, `X-Content-Type-Options`
  and `Referrer-Policy` headers.
- Set `APP_ENV=production` and `APP_DEBUG=false` on the live server.
