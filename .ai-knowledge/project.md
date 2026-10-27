# Project: Warehouse

> This is the only project-specific file in `.ai-knowledge/`. When you start a new project on this
> framework, rewrite this file and keep the others.

## Purpose

Records goods **entering** a warehouse: product, unit of measurement, quantity, unit price, total value
(quantity × price), entry date, supplier, document number, and the user who recorded it.
Outgoing goods and stock balances are **not** implemented yet. The planned next step is a `stock_issues` table
that the report subtracts.

- Local URL: `http://localhost/Framework/` (XAMPP). `/` redirects to `/admin` (the dashboard), and everything requires login.
- Database: MySQL/MariaDB `warehouse`, user `root`, empty password (`.env`).
- Currency: `APP_CURRENCY` (default `AZN`). Timezone: `Asia/Baku`.
- Languages: **Azerbaijani is the default** (`APP_LOCALE=az`), English is the second language and the
  fallback (`fallback_locale`), and Russian is also available.
- Documentation is bilingual: `ReadMe.md` is Azerbaijani (the main one) and `ReadMe.en.md` is English.
  When you change one, update the other in the same change. The `.ai-knowledge/` files stay English only.

## Tables

| Table | Key columns | Notes |
|-------|-------------|-------|
| `usertypes` | name, description, `can_*` ×9, is_locked | see auth-and-permissions.md |
| `users` | usertype_id → usertypes (RESTRICT), username, full_name, password, is_active | |
| `units` | name, symbol (both unique) | t, kg, g, pcs, L, m, m², m³, box, pack, roll, plt |
| `categories` | name (unique) | |
| `suppliers` | name (unique), phone, email, address | |
| `products` | sku (unique), name, category_id → categories (SET NULL), unit_id → units (RESTRICT), description | the unit is fixed once entries exist |
| `stock_entries` | product_id → products (RESTRICT), supplier_id (SET NULL), user_id (SET NULL), quantity DECIMAL(15,3), unit_price DECIMAL(15,2), total_value DECIMAL(20,2), entry_date DATE, document_no, note | `total_value` is computed in `StockEntry::save()` |

## Permissions (`App\Enums\Permission`)

`view_entries, create_entries, edit_entries, delete_entries, manage_products` (products, categories and units),
`manage_suppliers, view_reports, manage_users, manage_usertypes`.

Seeded types: **Administrator** (locked, everything), **Warehouse manager** (entries + products + suppliers + reports),
**Storekeeper** (view and create entries), **Accountant** (view entries + reports).

## Code map

| Area | Files |
|------|-------|
| Entries | `app/Controllers/Admin/StockEntryController.php` (+ CSV `export`), `app/Models/StockEntry.php` (`filtered()`, `relationsFor()`, `quantitiesFor()`), `resources/views/admin/entries/` |
| Report | `ReportController::stock`, `Product::stockSummary()`, `resources/views/admin/reports/stock.php` |
| Master data | `ProductController`, `CategoryController`, `UnitController`, `SupplierController` and their views |
| Users and permissions | `UserController`, `UserTypeController`, `App\Middleware\RequirePermission`, `App\Enums\Permission` |
| Dashboard | `DashboardController` (stats + 7-day value chart) |
| API | `app/Controllers/Api/StockController.php`: `/api/stock`, `/api/entries`, `/api/entries/{id}` |
| Formatting | `App\Support\Format::quantity()` / `money()` |
| Seeders | `UserTypeSeeder`, `AdminUserSeeder`, `UnitSeeder` (required), `DemoSeeder` (sample data, deterministic) |

## Demo accounts (from `Session.sql` / `DemoSeeder`)

`admin / admin12345`, `manager / manager123`, `storekeeper / storekeeper123`, `accountant / accountant123`.
These are development defaults only.

## Live deployment (InfinityFree)

- Domain `ecwarehouse.ct.ws` (also `dnp7vlic.infinityfree.com`), document root `/htdocs`; the root `.htaccess` sends
  everything to `public/`. MySQL host `sql304.infinityfree.com`; remote MySQL is blocked, so import data through
  phpMyAdmin or a one-time script on the server. The hosting dump must not contain `CREATE DATABASE` / `USE`.
- FTP only (no SFTP): `ftpupload.net:21` with explicit TLS, **TLS 1.2 max** (TLS 1.3 breaks uploads over ~15 KB).
  The VS Code SFTP extension (natizyskunk.sftp) is configured in `.vscode/sftp.json` (git-ignored, holds the password).
- Never upload the local `vendor/` (it has dev packages; the autoloader would require missing PHPUnit files) or the
  local `.env`. Build vendor with `composer install --no-dev --optimize-autoloader` in a clean copy and upload that.
- The server's `.env` is different from the local one (`APP_ENV=production`, `APP_DEBUG=false`, hosting DB details).

## The SQL dump (`Session.sql`)

It is generated from the real database, not written by hand. After any schema or seed change:

```bash
php console migrate:fresh --seed     # rebuilds the local `warehouse` database (destroys its data!)
```

Then dump it **with the tables listed in dependency order** (mysqldump's default is alphabetical, which puts `products`
before `units` and fails with errno 150 whenever someone runs part of the file without the `FOREIGN_KEY_CHECKS=0` header):

```bash
C:\xampp\mysql\bin\mysqldump.exe -uroot warehouse usertypes users units categories suppliers products stock_entries migrations --skip-dump-date --skip-comments --add-drop-table
```

Listing tables means no `--databases`, so the `CREATE DATABASE IF NOT EXISTS` / `USE` lines are written by hand after the
explanatory header comment. Also replace mysqldump's `/*!40101 SET @OLD_…*/` header and its `SET …=@OLD_…` footer with
plain `SET NAMES utf8mb4; SET FOREIGN_KEY_CHECKS = 0;` at the top and `SET FOREIGN_KEY_CHECKS = 1;` at the end: users
paste parts of the file into phpMyAdmin, and the footer fails (#1231 time_zone NULL) when the header was not run.
Add new tables to that list after the tables they reference. Check that the file imports
into a scratch database.
Ask the user before running `migrate:fresh` on a database that may contain real data.
