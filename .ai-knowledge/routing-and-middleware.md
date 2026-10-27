# Routing and middleware

## Defining routes

`routes/web.php` and `routes/api.php` each return a closure that receives the `Router`.

```php
$router->get('/posts', [PostController::class, 'index'])->name('posts.index');
$router->post('/posts', [PostController::class, 'store'])->name('posts.store')->middleware('can:create_posts');
$router->get('/posts/{id:\d+}/edit', [PostController::class, 'edit'])->name('posts.edit');
$router->put(...); $router->patch(...); $router->delete(...);
```

- Placeholders: `{name}` matches one path segment, and `{name:regex}` uses a custom pattern (e.g. `{locale:[a-z]{2}}`).
  Always constrain numeric ids with `{id:\d+}`. Without it, `/posts/create` could match `/posts/{id}`.
- Routes are matched **in order of registration**, and the first match wins.
- Actions are `[Controller::class, 'method']` or a closure. Prefer controllers.
- `HEAD` is answered by `GET` routes.

## Resource routes

```php
$router->resource('posts', PostController::class);
// GET    /posts             posts.index
// GET    /posts/create      posts.create
// POST   /posts             posts.store
// GET    /posts/{id}/edit   posts.edit
// PUT    /posts/{id}        posts.update
// DELETE /posts/{id}        posts.destroy
```

There is no `show` route. When different actions need different permissions, register the routes one by one
instead (see the `entries` routes in `routes/web.php`).

## Groups

```php
$router->group(['prefix' => 'admin', 'as' => 'admin.', 'middleware' => ['auth', 'can']], function (Router $router): void {
    $router->group(['middleware' => 'can:manage_users'], function (Router $router): void {
        $router->resource('users', UserController::class);   // name: admin.users.index, URL: /admin/users
    });
});
```

Prefixes, name prefixes (`as`) and middleware are inherited by nested groups.

## Generating URLs

- `route('admin.users.edit', ['id' => 5])` → `/Framework/admin/users/5/edit`
- `route('admin.entries.index', [], ['product_id' => 3])` adds a query string (the third argument).
- `to_route('name', [...])` returns a RedirectResponse.
- `php console route:list` prints every route with its name and middleware.

## Middleware

A middleware implements `Framework\Http\Middleware\Middleware`:

```php
final readonly class EnsureSomething implements Middleware
{
    public function __construct(private Auth $auth) {}   // autowired

    public function handle(Request $request, Closure $next, string ...$parameters): Response
    {
        // $parameters come from "alias:a,b"
        return $next($request);
    }
}
```

Register a short alias in `config/middleware.php` → `aliases`, then use `'alias'` or `'alias:param1,param2'`.

| Alias / group | Class | Effect |
|---------------|-------|--------|
| `web` (group) | VerifyCsrfToken | 419 on POST/PUT/PATCH/DELETE without a valid `_token` field or `X-CSRF-TOKEN` header |
| `api` (group) | — | no CSRF check; use for JSON routes |
| `auth` | Authenticate | guests are redirected to `login` (JSON: 401) and the intended URL is remembered |
| `guest` | RedirectIfAuthenticated | signed-in users are sent away from the login page |
| `can` | App\Middleware\RequirePermission | `can` alone: the account must be active. `can:perm1,perm2`: all of these are required (403 otherwise) |

Global middleware (every request): `StartSession` and `SetLocale` (reads `locale` from the session).

## Forms with PUT/DELETE

HTML forms can only send POST, so add `<?= method_field('DELETE') ?>`. `Request::capture()` reads the
`_method` field for PUT, PATCH and DELETE.
