<?php
/** @var \Framework\View\Template $this */
/** @var string $title */
/** @var \App\Models\User $user */
use App\Enums\Permission;

$user = auth()->user();
$current = request()->route()?->getName() ?? '';

// Each link is shown only to users whose type has the permission (null = everyone).
$links = array_filter([
    ['route' => 'admin.dashboard', 'match' => 'admin.dashboard', 'permission' => null, 'label' => __('nav.dashboard'), 'icon' => 'M3 13h8V3H3zm0 8h8v-6H3zm10 0h8V11h-8zm0-18v6h8V3z'],
    ['route' => 'admin.entries.index', 'match' => 'admin.entries.', 'permission' => Permission::ViewEntries, 'label' => __('nav.entries'), 'icon' => 'M12 3 4 7v10l8 4 8-4V7zm0 2.2L17.6 8 12 10.8 6.4 8zM6 9.6l5 2.5v6.3l-5-2.5zm7 8.8v-6.3l5-2.5v6.3z'],
    ['route' => 'admin.products.index', 'match' => 'admin.products.', 'permission' => Permission::ManageProducts, 'label' => __('nav.products'), 'icon' => 'M4 6h16v2H4zm0 5h16v2H4zm0 5h16v2H4z'],
    ['route' => 'admin.categories.index', 'match' => 'admin.categories.', 'permission' => Permission::ManageProducts, 'label' => __('nav.categories'), 'icon' => 'M3 5h8v6H3zm10 0h8v6h-8zM3 13h8v6H3zm10 0h8v6h-8z'],
    ['route' => 'admin.units.index', 'match' => 'admin.units.', 'permission' => Permission::ManageProducts, 'label' => __('nav.units'), 'icon' => 'M3 8h18v8H3zm2 2v4h1v-2h1v2h1v-4zm5 0v4h1v-2h1v2h1v-4zm5 0v4h1v-2h1v2h1v-4z'],
    ['route' => 'admin.suppliers.index', 'match' => 'admin.suppliers.', 'permission' => Permission::ManageSuppliers, 'label' => __('nav.suppliers'), 'icon' => 'M2 7h12v9H2zm12 3h4l3 3v3h-7zM6 19a2 2 0 1 0 0-4 2 2 0 0 0 0 4m11 0a2 2 0 1 0 0-4 2 2 0 0 0 0 4'],
    ['route' => 'admin.reports.stock', 'match' => 'admin.reports.', 'permission' => Permission::ViewReports, 'label' => __('nav.reports'), 'icon' => 'M4 20V10h4v10zm6 0V4h4v16zm6 0v-7h4v7z'],
    ['route' => 'admin.users.index', 'match' => 'admin.users.', 'permission' => Permission::ManageUsers, 'label' => __('nav.users'), 'icon' => 'M12 12a4 4 0 1 0 0-8 4 4 0 0 0 0 8m0 2c-4 0-8 2-8 5v1h16v-1c0-3-4-5-8-5'],
    ['route' => 'admin.usertypes.index', 'match' => 'admin.usertypes.', 'permission' => Permission::ManageUsertypes, 'label' => __('nav.usertypes'), 'icon' => 'M12 2 4 5v6c0 5 3.4 9.7 8 11 4.6-1.3 8-6 8-11V5zm-1 14-4-4 1.4-1.4 2.6 2.6 5.6-5.6L18 9z'],
], static fn (array $link) => $link['permission'] === null || $user->can($link['permission']));
?>
<!DOCTYPE html>
<html lang="<?= e(app(\Framework\Translation\Translator::class)->getLocale()) ?>">
<head>
<?= $this->insert('partials/meta', ['title' => config('app.name'), 'private' => true]) ?>
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
    <link rel="stylesheet" href="<?= e(asset('assets/css/admin.css')) ?>">
</head>
<body class="admin">
    <aside class="sidebar" id="sidebar">
        <a class="brand" href="<?= e(route('admin.dashboard')) ?>">
            <img src="<?= e(asset('assets/img/logo.svg')) ?>" alt="" width="34" height="34">
            <span><?= e(config('app.name')) ?></span>
        </a>

        <nav class="sidebar__nav">
            <?php foreach ($links as $link): ?>
                <a href="<?= e(route($link['route'])) ?>" class="<?= str_starts_with($current, $link['match']) ? 'is-active' : '' ?>">
                    <svg viewBox="0 0 24 24" width="18" height="18" aria-hidden="true"><path fill="currentColor" d="<?= e($link['icon']) ?>"/></svg>
                    <?= e($link['label']) ?>
                </a>
            <?php endforeach ?>
        </nav>

    </aside>

    <div class="admin-main">
        <header class="topbar">
            <button type="button" class="topbar__menu" data-toggle="sidebar" aria-controls="sidebar" aria-label="<?= e(__('nav.menu')) ?>">
                <svg viewBox="0 0 24 24" width="22" height="22" aria-hidden="true"><path fill="currentColor" d="M3 6h18v2H3zm0 5h18v2H3zm0 5h18v2H3z"/></svg>
            </button>
            <h1 class="topbar__title"><?= e($title) ?></h1>

            <div class="topbar__end">
                <?= $this->insert('partials/locale-switcher') ?>
                <span class="user-chip">
                    <strong><?= e($user->displayName()) ?></strong>
                    <small><?= e($user->userType()->name) ?></small>
                </span>
                <form method="post" action="<?= e(route('logout')) ?>">
                    <?= csrf_field() ?>
                    <button type="submit" class="btn btn--ghost"><?= e(__('nav.logout')) ?></button>
                </form>
            </div>
        </header>

        <main class="admin-content">
            <?= $this->insert('partials/flash') ?>
            <?= $this->section('content') ?>
        </main>
    </div>

    <?= $this->insert('partials/confirm-dialog') ?>
    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
