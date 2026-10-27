# Console and testing

## Console (`php console <command>`)

| Command | Purpose |
|---------|---------|
| `serve [--host= --port=]` | PHP built-in server (alternative to XAMPP Apache) |
| `migrate [--seed] [--pretend]` | run pending migrations; `--pretend` prints the SQL instead |
| `migrate:status`, `migrate:rollback [--step=N]`, `migrate:reset`, `migrate:fresh [--seed]` | destructive ones need `--force` when `APP_ENV=production` |
| `db:seed [--class=Name]` | default `DatabaseSeeder` |
| `make:migration name [--create=t / --table=t]`, `make:model Name [--migration]`, `make:controller Admin/NameController`, `make:seeder Name` | generators (they refuse to overwrite) |
| `route:list` | every route with its name and middleware |
| `user:create <username> <password> [--type="User type name"]` | project command |

### Writing a command

Put it in `app/Console/Commands/`. It is discovered automatically:

```php
#[AsCommand('stock:check', 'Describe what it does', '<arg> [--flag] [--opt=value]')]
final class StockCheckCommand extends Command
{
    public function handle(): int
    {
        $arg = $this->argument(0);              // positional, ?string
        $opt = $this->option('opt', 'default'); // --opt=value → string, --flag → true
        $this->info('ok'); $this->comment('note'); $this->error('bad'); $this->table(['A'], [['1']]);

        return self::SUCCESS;                   // or self::FAILURE
    }
}
```

The constructor is autowired, but must accept `Application $app` first and pass it to `parent::__construct($app)`.
Destructive commands call `if (!$this->confirmProduction()) return self::FAILURE;`.

## Tests

```bash
composer install            # once, for PHPUnit
php vendor/bin/phpunit      # or: composer test
php vendor/bin/phpunit --filter HttpTest
```

- `phpunit.xml` forces `DB_CONNECTION=sqlite` and `DB_DATABASE=:memory:`. **Tests never touch MySQL**, so SQL
  must work on SQLite too.
- `tests/Unit/*` extend PHPUnit's TestCase and test framework classes in isolation (build a `new Connection([...])`,
  a `new Validator(...)`, and so on).
- `tests/Feature/*` extend `Tests\TestCase`, which boots the app and runs all migrations for every test (seeders do **not** run).
  - `$this->createUser([Permission::A, Permission::B], 'name', 'password123', active: true)` creates a user
    and a user type with exactly those permissions. With no permissions given, it creates a locked Administrator type.
  - `$this->actingAs($user)` signs in.
  - `$this->call('POST', '/admin/posts', [...])` sends the request through the full HTTP kernel (the CSRF token is added for you).
    It returns a Response: `->status()`, `->content()`, `->url()` for redirects, `->data()` for JSON.
  - After a failed validation: `$_SESSION['_flash']['_errors']` and `$_SESSION['_flash']['_old']`.
- For every new feature, test the happy path, a validation failure, and the permission (403) for a user without it.
- The demo/seed data is not available in tests, so create what you need inside each test.
