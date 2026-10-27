# Gotchas

Things that have caused bugs or surprises in this codebase.

## PHP and runtime

- **Warnings are exceptions.** `Application::registerErrorHandling()` converts every warning or notice into an
  `ErrorException`, so an undefined array key or index is a 500, not a notice. Use `??`, `isset()` and
  `array_key_exists()` deliberately. Inside `$map[$key]->prop ?? '—'`, the `??` covers the missing key.
- **`DateTimeImmutable::createFromFormat()` returns `false`, not `null`**, on failure, so `?->format()` does
  not protect you. Use `Framework\Validation\Validator::isDate($s)`.
- `$request->integer('x')` returns the default for `0` too (it uses `?:`). Treat 0 as "not selected".
- Model attributes are raw database values until read through `getAttribute()`/`__get` with a cast. Compare ids
  only after casting (`'id' => 'int'` in `$casts`), or `===` between `"5"` and `5` fails.
- The model's `fill()` silently ignores keys that are not in `$fillable`. If a field "does not save", check `$fillable` first.

## Database

- Tests run on **SQLite**, production on **MySQL/MariaDB**. Avoid MySQL-only SQL (backticks, `IFNULL`,
  `GROUP_CONCAT` with options, `ON DUPLICATE KEY`). Use `COALESCE`, standard `JOIN`s, and unquoted simple identifiers.
- DECIMAL values come back from MySQL as strings. Cast them to `float` in the model.
- In a LEFT JOIN, put the date filters in the `ON` clause, not in `WHERE`, or rows without matches disappear
  (see `Product::stockSummary`).
- `QueryBuilder` methods mutate the builder. Clone it before reusing a base query for a count or sum and a page.
- MySQL creates an index for every foreign key. Don't add `->index()` to `foreignId` columns.
- Deleting a row referenced by a RESTRICT foreign key throws a `PDOException` (500). Check first
  (`isUsed()`, `hasEntries()`, `usersCount()`) and return a friendly flash error.

## HTTP and views

- Every POST/PUT/DELETE in the `web` group needs `csrf_field()`, or you get a 419 "page expired".
- Unchecked checkboxes are **absent** from the request. Use `$request->has('flag')`, not `validate()`.
- `old()` returns the default when the key is missing, which is the same thing that happens for an unchecked box.
  See the checkbox note in views-and-frontend.md.
- A route like `/posts/{id}` without `:\d+` swallows `/posts/create`. Constrain ids.
- Hiding a button in a view is not access control. Every route needs its `can:` middleware.
- Use `url()`, `route()` and `asset()`. Hard-coded `/admin` breaks when the app lives in `http://localhost/Framework/`.
- A raw translation key appearing on screen (`products.title`) means the key is missing in that language file.

## Tooling (for AI agents on Windows/XAMPP)

- The PHP CLI is `C:\xampp\php\php.exe` (on PATH as `php`). The MySQL client is `C:\xampp\mysql\bin\mysql.exe`
  (not on PATH), and `mysqldump.exe` is next to it.
- Writing PHP files through shell heredocs is fragile because of quoting (`$`, `'`, backticks). Use the editor's file-writing tool instead.
- `.env` is not committed. `.env.example` is, so keep it in sync when you add settings.
