# Controllers, requests, responses, validation

## Controller shape

```php
namespace App\Controllers\Admin;

final readonly class PostController           // readonly when it only holds injected services
{
    private const RULES = ['title' => 'required|string|min:2|max:100'];

    public function __construct(private Auth $auth) {}          // services: constructor

    public function index(Request $request): Response           // Request / services: method injection too
    {
        $search = $request->string('q');
        $posts = Post::query()->search(Post::SEARCHABLE, $search)->latest()->paginate(20, $request->integer('page', 1));

        return view('admin/posts/index', ['posts' => $posts, 'search' => $search]);
    }

    public function update(int $id, Request $request): RedirectResponse   // {id} arrives cast to int
    {
        Post::findOrFail($id)->update($request->validate(self::RULES)); // findOrFail → 404

        return to_route('admin.posts.index')->with('success', __('posts.updated'));
    }
}
```

Conventions used across the codebase:
- Use a `RULES` constant when the rules are static, and a `private function rules(?Model $m = null)` when
  `unique` must ignore the current row (`'unique:posts,slug' . ($post ? ",{$post->id}" : '')`).
- Use a `private function form(?Model $m)` helper when create and edit share a view with extra select-box data.
- `store`/`update`/`destroy` redirect with a flash message (`success`, `warning` or `error`).
- Business-rule refusals (e.g. "in use, cannot delete") use `return back()->with('error', __('...'))`.
- For a refusal that belongs to one form field, `throw new ValidationException(['field' => [__('...')]])`
  so the message appears under that field and old input is kept.

## Request

| Method | Notes |
|--------|-------|
| `$request->string('q')` | trimmed string, `''` by default |
| `$request->integer('page', 1)` | int, or the default when missing or invalid (also when the value is `0`) |
| `$request->input('key', $default)` | raw value, dot notation; arrays such as `permissions[]` come back as arrays |
| `$request->has('is_active')` | use this for checkboxes (an unchecked box is not sent at all) |
| `$request->all()`, `only(...)`, `queryAll()` | |
| `$request->validate($rules)` | returns **only** the validated fields; throws `ValidationException` |
| `$request->path`, `->method`, `->ip()`, `->header()`, `->expectsJson()` | |

## Validation

Rules are strings joined with `|`: `required, nullable, string, integer, numeric, boolean, email, date (Y-m-d),
min:n, max:n, in:a,b, alpha_dash, confirmed, unique:table,column[,ignoreId], exists:table,column`.

- `min`/`max` compare the **value** when the field also has `integer` or `numeric`, and the length otherwise.
- An empty optional field skips its other rules and is **returned as `null`**, so it can go straight
  into a nullable column.
- Values are trimmed.
- Messages come from `lang/*.php` under `validation.*`. Field names come from `attributes.<field>`, so add
  every new field name there in every language.
- `confirmed` expects a `<field>_confirmation` input.
- Need a new rule? Add it to `Framework\Validation\Validator::check()` together with a `validation.<rule>`
  message in every language and a unit test.
- `Validator::isDate($string)` is public and reusable for filters.

On failure in the browser, the user is redirected back. In views, use `error('field')` for the first message
and `old('field', $default)` for the previous input. Passwords and tokens are never flashed.

## Responses and helpers

| Helper | Returns |
|--------|---------|
| `view('admin/posts/index', [...], $status = 200)` | HTML Response |
| `json($data, $status = 200)` | JsonResponse (Paginator and Models serialize themselves) |
| `to_route($name, $params)`, `redirect($pathOrUrl)`, `back()` | RedirectResponse |
| `->with('success', $msg)` | flash for the next request (shown by `partials/flash`) |
| `abort(403)` / `throw new HttpException(404)` | error page |
| `new Response($body, 200, ['Content-Type' => 'text/csv; charset=utf-8', 'Content-Disposition' => 'attachment; filename="x.csv"'])` | file download (see `StockEntryController::export`) |

Other helpers (in `framework/helpers.php`): `app()`, `config()`, `env()`, `__()`, `e()`, `url()`, `route()`, `asset()`,
`request()`, `session()`, `auth()`, `csrf_token()`, `csrf_field()`, `method_field()`, `old()`, `error()`,
`base_path()`, `storage_path()`.

## Session and auth in controllers

- `auth()->user()` (or the injected `Auth`) returns the model or null, and `auth()->id()` the id.
- `session()->put/get/pull/forget/flash/getFlash`.
- Logging: inject `Framework\Logging\Logger` and call `->error($e)`, `->warning()` or `->info()`.
