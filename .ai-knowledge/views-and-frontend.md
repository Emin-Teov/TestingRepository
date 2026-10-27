# Views, frontend, translations

## Templates

Plain PHP in `resources/views`. `view('admin/posts/index', $data)` loads `resources/views/admin/posts/index.php`.
Inside a template, `$this` is a `Framework\View\Template`.

```php
<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $posts */
use App\Enums\Permission;                    // `use` statements are allowed at the top

$this->extend('layouts/admin', ['title' => __('posts.title')]);   // the layout gets $title
/** @var \App\Models\User $user */
$user = auth()->user();
?>
<h2><?= e($post->title) ?></h2>                <!-- ALWAYS e() -->
<?= $this->insert('partials/search', ['search' => $search]) ?>    <!-- partial with its own data only -->
<?php $this->start('scripts') ?> ... <?php $this->stop() ?>       <!-- named section; layout prints $this->section('scripts') -->
```

- Layouts: `layouts/admin` (sidebar, top bar, flash messages, confirm dialog) and `layouts/app`
  (public pages: login, errors).
- The admin sidebar links live in an array at the top of `layouts/admin.php`. Each link has a `permission`
  (or `null` for everyone) and an SVG path `icon` (24×24 viewBox).
- Partials: `flash`, `pagination` (`['paginator' => $p]`, keeps the query string), `search` (`?q=`),
  `confirm-dialog`, `locale-switcher`.
- Output raw HTML (`<?= ... ?>` without `e()`) **only** for framework helpers that already return safe HTML:
  `csrf_field()`, `method_field()`, `$this->insert()`, `$this->section()`.

## Standard page patterns (copy an existing page)

- **List page:** `.toolbar` (search + "+ New" button), `.empty` when there are no rows, then
  `.card.table-wrap > table.table`. Give every `<td>` a `data-label` (phones show rows as stacked cards),
  put buttons in a `.table__actions` cell, and add the pagination partial at the bottom.
  See `resources/views/admin/suppliers/index.php`.
- **Form page:** `form.card.form.form--narrow`, with `.field` labels, `.form__row` for two columns and `.form__actions` at
  the bottom. Each input shows `error('x')` and `aria-invalid`, and gets its value from `old('x', $model?->x)`.
  Use one view for both create and edit (`$editing = $model !== null`). See `resources/views/admin/suppliers/form.php`.
- **Delete:** `<form method="post" data-confirm="<?= e(__('...delete_confirm')) ?>">` with `csrf_field()` and
  `method_field('DELETE')`. `app.js` shows the confirm dialog.
- **Checkbox:** `label.check > input[type=checkbox] + span`. Reading old input of a checkbox after a failed
  submit: if `old('<some required text field>', null) !== null`, the form was submitted, so a missing
  checkbox means it was unchecked.
- **Filters:** `form.card.filters[method=get]` with `.field`s and `.filters__actions`.
- Hide buttons the user may not use: `<?php if ($user->can(Permission::X)): ?>`. The route must still check the permission too.

## CSS

- `public/assets/css/app.css` (shared) and `admin.css` (admin layout and components). No build step.
- **Design tokens** are on `:root` in `app.css`: `--bg --surface --surface-2 --border --text --text-muted
  --accent --accent-strong --danger --good --warning --header --radius --shadow --font`. The theme is dark.
  Change the colours there instead of hard-coding them.
- Useful classes: `.btn .btn--ghost .btn--danger .btn--small .btn--block`, `.card`, `.muted`, `.nowrap`,
  `.num` (right-aligned tabular numbers), `.d-block`, `.badge .badge--good .badge--warning .badge--admin`,
  `.alert--success|warning|error`, `.stats > .stat`, `.grid-2`, `.list`, `.details`, `.visually-hidden`.
- Breakpoints: 900px (the sidebar becomes a drawer) and 640px (tables stack).
- `asset('assets/css/app.css')` adds `?v=<mtime>` for cache busting.

## JavaScript

`public/assets/js/app.js`, written in vanilla ES2020 without a framework. Behaviour hooks are `data-*` attributes:
`data-dismiss` (closes an alert), `data-copy`, `form[data-confirm]`, `data-toggle="sidebar"`, `data-print`,
and `[data-entry-form]` (example of a page-specific enhancement). Add new behaviour the same way:
`document.querySelectorAll('[data-x]').forEach(...)`. Pages must also work without JS. For fetch POSTs,
send the `X-CSRF-TOKEN` header from `<meta name="csrf-token">`.

## Translations

- `lang/en.php`, `az.php`, `ru.php` hold nested arrays grouped by screen (`common`, `nav`, `auth`, one group per
  module, `permissions`, `validation`, `attributes`, `errors`).
- `__('posts.created')` and `__('auth.welcome', ['name' => $n])` replace `:name`. A missing key falls back to
  `en`, then to the key itself. So a raw key showing on screen means the key is missing.
- **Every key must exist in every language file.** The structure is identical across the files.
- The available languages are `config('app.locales')`, and each needs `lang/<code>.php` and `public/assets/flags/<code>.png`.
  The user's choice is stored in the session by `LocaleController`.
