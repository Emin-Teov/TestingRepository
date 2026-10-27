<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\User $user */
/** @var list<\App\Models\UserType> $types */
$editing = $user !== null;
$this->extend('layouts/admin', ['title' => $editing ? __('users.edit') : __('users.new')]);
$selectedType = (int) old('usertype_id', $user?->usertype_id);
// A failed submit flashes old input; an unchecked box is simply missing from it.
$hasOldInput = old('username', null) !== null;
$active = $hasOldInput ? old('is_active', null) !== null : ($user?->is_active ?? true);
?>
<form class="card form form--narrow" method="post"
      action="<?= e($editing ? route('admin.users.update', ['id' => $user->id]) : route('admin.users.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('users.username')) ?></span>
            <input type="text" name="username" value="<?= e(old('username', $user?->username)) ?>" required minlength="2" maxlength="55"
                   autocomplete="off" <?= error('username') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('username')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('users.full_name')) ?></span>
            <input type="text" name="full_name" value="<?= e(old('full_name', $user?->full_name)) ?>" maxlength="100"
                   <?= error('full_name') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('full_name')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>
    </div>

    <label class="field">
        <span><?= e(__('users.usertype')) ?></span>
        <select name="usertype_id" required <?= error('usertype_id') ? 'aria-invalid="true"' : '' ?>>
            <option value=""><?= e(__('common.choose')) ?></option>
            <?php foreach ($types as $type): ?>
                <option value="<?= e($type->id) ?>" <?= $type->id === $selectedType ? 'selected' : '' ?>><?= e($type->name) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($message = error('usertype_id')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <label class="check">
        <input type="checkbox" name="is_active" value="1" <?= $active ? 'checked' : '' ?>>
        <span><?= e(__('users.active_hint')) ?></span>
    </label>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('users.password')) ?></span>
            <input type="password" name="password" autocomplete="new-password" minlength="8" maxlength="255"
                   <?= $editing ? '' : 'required' ?> <?= error('password') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($editing): ?><small class="field__hint"><?= e(__('users.password_hint')) ?></small><?php endif ?>
            <?php if ($message = error('password')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('users.password_confirmation')) ?></span>
            <input type="password" name="password_confirmation" autocomplete="new-password" maxlength="255" <?= $editing ? '' : 'required' ?>>
        </label>
    </div>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.users.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
