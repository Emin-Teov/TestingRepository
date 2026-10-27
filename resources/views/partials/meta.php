<?php
/** @var \Framework\View\Template $this */
/** @var string $title Full page title. */
/** @var bool $private Pages behind the login: keep them out of search engines. */
$description = __('meta.description');
$private ??= false;
?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title><?= e($title) ?></title>
    <meta name="description" content="<?= e($description) ?>">
    <meta name="author" content="E100COMP">
    <meta name="application-name" content="<?= e(config('app.name')) ?>">
    <meta name="theme-color" content="#141e2a">
    <meta name="robots" content="<?= $private ? 'noindex, nofollow' : 'index, follow' ?>">
    <meta name="csrf-token" content="<?= e(csrf_token()) ?>">
    <meta property="og:type" content="website">
    <meta property="og:site_name" content="<?= e(config('app.name')) ?>">
    <meta property="og:title" content="<?= e($title) ?>">
    <meta property="og:description" content="<?= e($description) ?>">
<?php if (str_starts_with(url('/'), 'http')): /* og:image must be absolute, so only when APP_URL is set */ ?>
    <meta property="og:image" content="<?= e(url('assets/img/logo.png')) ?>">
<?php endif ?>
    <link rel="icon" href="<?= e(asset('assets/img/logo.svg')) ?>" type="image/svg+xml">
    <link rel="alternate icon" href="<?= e(asset('assets/img/logo.png')) ?>" type="image/png">
    <link rel="apple-touch-icon" href="<?= e(asset('assets/img/logo.png')) ?>">
