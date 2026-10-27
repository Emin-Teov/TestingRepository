<?php
/** @var \Framework\View\Template $this */
$this->extend('layouts/app', ['title' => __('auth.title')]);
?>
<div class="auth">
    <form class="card form auth__card" method="post" action="<?= e(route('login.store')) ?>" novalidate>
        <?= csrf_field() ?>
        <h1><?= e(__('auth.title')) ?></h1>

        <label class="field">
            <span><?= e(__('users.username')) ?></span>
            <input type="text" name="username" value="<?= e(old('username')) ?>" placeholder="<?= e(__('auth.username')) ?>"
                   autocomplete="username" required maxlength="55" autofocus
                   <?= error('username') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('username')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('users.password')) ?></span>
            <input type="password" name="password" placeholder="<?= e(__('auth.password')) ?>"
                   autocomplete="current-password" required maxlength="255"
                   <?= error('password') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('password')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <button type="submit" class="btn btn--block"><?= e(__('auth.submit')) ?></button>
    </form>
</div>
