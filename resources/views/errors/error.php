<?php
/** @var \Framework\View\Template $this */
/** @var int $status */
/** @var string $message */
/** @var ?\Throwable $exception */
$this->extend('layouts/app', ['title' => (string) $status]);
?>
<div class="error-page">
    <p class="error-page__code"><?= e($status) ?></p>
    <p class="error-page__message"><?= e($message) ?></p>
    <a class="btn" href="<?= e(route('home')) ?>"><?= e(__('errors.back_home')) ?></a>

    <?php if ($exception !== null): ?>
        <details class="card error-page__debug" open>
            <summary><?= e($exception::class) ?> — <?= e($exception->getFile()) ?>:<?= e($exception->getLine()) ?></summary>
            <pre><?= e($exception->getTraceAsString()) ?></pre>
        </details>
    <?php endif ?>
</div>
