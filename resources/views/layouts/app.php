<?php
/** @var \Framework\View\Template $this */
/** @var string|null $title */
$user = auth()->user();
?>
<!DOCTYPE html>
<html lang="<?= e(app(\Framework\Translation\Translator::class)->getLocale()) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <title><?= e(isset($title) ? $title . ' · ' . config('app.name') : config('app.name')) ?></title>
    <link rel="icon" href="<?= e(asset('assets/img/logo.png')) ?>" type="image/png">
    <link rel="stylesheet" href="<?= e(asset('assets/css/app.css')) ?>">
</head>
<body class="site">
    <header class="site-header">
        <div class="container site-header__inner">
            <a class="brand" href="<?= e(route('home')) ?>">
                <img src="<?= e(asset('assets/img/logo.png')) ?>" alt="" width="32" height="32">
                <span><?= e(config('app.name')) ?></span>
            </a>

            <nav class="site-nav">
                <?= $this->insert('partials/locale-switcher') ?>

                <?php if ($user !== null): ?>
                    <a class="btn btn--ghost" href="<?= e(route('admin.dashboard')) ?>"><?= e(__('nav.dashboard')) ?></a>
                    <form method="post" action="<?= e(route('logout')) ?>">
                        <?= csrf_field() ?>
                        <button type="submit" class="btn"><?= e(__('nav.logout')) ?></button>
                    </form>
                <?php else: ?>
                    <a class="btn" href="<?= e(route('login')) ?>"><?= e(__('nav.login')) ?></a>
                <?php endif ?>
            </nav>
        </div>
    </header>

    <main class="container site-main">
        <?= $this->insert('partials/flash') ?>
        <?= $this->section('content') ?>
    </main>

    <footer class="site-footer">
        <div class="container site-footer__inner">
            <span>&copy; <?= date('Y') ?> <?= e(config('app.name')) ?></span>
            <div class="contacts">
                <a href="tel:<?= e(config('app.contact.phone')) ?>" title="<?= e(config('app.contact.phone')) ?>"><?= e(config('app.contact.phone')) ?></a>
                <button type="button" class="link" data-copy="<?= e(config('app.contact.email')) ?>" data-copied="<?= e(__('footer.copied')) ?>">
                    <?= e(config('app.contact.email')) ?>
                </button>
                <a href="<?= e(config('app.contact.linkedin')) ?>" target="_blank" rel="noopener">LinkedIn</a>
            </div>
        </div>
    </footer>

    <script src="<?= e(asset('assets/js/app.js')) ?>" defer></script>
</body>
</html>
