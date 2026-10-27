# Architecture

## Folders

```
app/                     Project code (namespace App\)
  Console/Commands/      Custom CLI commands, auto-discovered by #[AsCommand]
  Controllers/           HTTP controllers (Admin/, Api/ sub-namespaces by convention)
  Enums/                 Backed enums (e.g. Permission)
  Middleware/            Project middleware (e.g. RequirePermission)
  Models/                Active-record models extending Framework\Database\Model
  Support/               Small project helpers (e.g. Format)
bootstrap/app.php        Creates the Application and registers singletons
bootstrap/autoload.php   Composer autoloader, or a PSR-4 fallback when vendor/ is missing
config/*.php             Plain arrays; read with config('file.key')
database/migrations/     YYYY_MM_DD_NNNNNN_description.php, each returns an anonymous Migration
database/seeders/        Namespace Database\Seeders\, entry point DatabaseSeeder
framework/               The framework (namespace Framework\). Shared by all projects.
lang/{locale}.php        Translation arrays
public/                  Web root: index.php, assets/css, assets/js, assets/img, assets/flags
resources/views/         Plain-PHP templates
routes/web.php, api.php  Each returns function (Router $router): void
storage/                 logs/, cache/ (rate limits), database/ (SQLite). Not committed.
tests/                   PHPUnit: Unit/ (no app boot) and Feature/ (full app, in-memory SQLite)
console                  CLI entry point: php console <command>
.htaccess                Rewrites everything into public/ (works in a sub-folder such as /Framework)
```

## Request lifecycle

1. `public/index.php` loads `bootstrap/app.php`. That creates the `Application` (which is also the DI container),
   loads `.env` and `config/`, sets the timezone and installs an error handler that **turns every
   warning or notice into an `ErrorException`**.
2. `Framework\Http\Kernel::handle(Request::capture())`:
   - runs the **global middleware** from `config/middleware.php` (`StartSession`, `SetLocale`);
   - `Router::match()` finds the route (404 when nothing matches, 405 when only the method is wrong);
   - runs the route's middleware (group names and aliases are expanded from `config/middleware.php`);
   - calls the controller through `Container::call()`;
   - converts the return value to a Response: `Response` as is, array or `JsonSerializable` → JSON,
     string → HTML, `null` → 204;
   - adds security headers (`X-Content-Type-Options`, `X-Frame-Options`, `Referrer-Policy`).
3. Any exception goes to `Framework\Exceptions\Handler`:
   - `ValidationException` → redirect back with errors and old input (JSON 422 for API requests);
   - `ModelNotFoundException` → 404;
   - `HttpException($status)` → that status;
   - anything else → logged to `storage/logs/app-YYYY-MM-DD.log` and returned as 500 (with a trace
     only when `APP_DEBUG=true`).
   - HTML errors render `resources/views/errors/error.php`. JSON is used when the path starts with `/api`,
     the request sends `Accept: application/json`, or it is an XHR request.

## Container (dependency injection)

`Framework\Container\Container`, with `Application` extending it.

- `app()` returns the container, and `app(Foo::class)` resolves a class.
- **Autowiring:** constructor parameters with class types are resolved automatically. No registration is
  needed unless a constructor takes scalars.
- `singleton(id, fn)`, `bind(id, fn)` and `instance(id, $obj)` are called in `bootstrap/app.php`.
  Singletons: Logger, Connection, Schema, Migrator, Session, Translator, View, Auth, Router.
- `call([Class, 'method'], $params)` injects services by type and route parameters **by name**, casting
  them to the declared scalar type (`int $id` receives an int).

## Configuration

- `.env` is read by `Framework\Support\Env`, and real environment variables win over the file. The values
  `true`, `false`, `null` and `empty` are converted. Read `.env` **only inside `config/*.php`** with
  `env('KEY', default)`, and use `config('app.currency')` everywhere else.
- `config/app.php`: name, env, debug, url, timezone, currency, locale, fallback_locale, locales, session, contact.
- `config/auth.php`: user model, username column, login throttling.
- `config/database.php`: default connection, `mysql` and `sqlite` connections.
- `config/middleware.php`: `global`, `groups` (`web` adds CSRF, `api` is empty) and `aliases` (`auth`, `guest`, `can`).

## URLs in a sub-folder

`Request::detectBasePath()` finds the prefix (e.g. `/Framework`). Always build links with `url()`,
`route()` or `asset()`, never hard-coded `/admin/...`. Set `APP_URL` only when auto-detection is wrong.
