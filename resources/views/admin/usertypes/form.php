<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\UserType $type */
use App\Enums\Permission;

$editing = $type !== null;
$locked = $type?->is_locked ?? false;
$this->extend('layouts/admin', ['title' => $editing ? __('usertypes.edit') : __('usertypes.new')]);

// After a failed submit, show what was checked (unchecked boxes are not sent at all); otherwise what is stored.
$granted = old('name', null) !== null
    ? (array) old('permissions', [])
    : array_map(static fn (Permission $permission) => $permission->value, $type?->permissions() ?? [Permission::ViewEntries]);
?>
<form class="card form form--narrow" method="post"
      action="<?= e($editing ? route('admin.usertypes.update', ['id' => $type->id]) : route('admin.usertypes.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <label class="field">
        <span><?= e(__('usertypes.name')) ?></span>
        <input type="text" name="name" value="<?= e(old('name', $type?->name)) ?>" required minlength="2" maxlength="55" autofocus
               <?= error('name') ? 'aria-invalid="true"' : '' ?>>
        <?php if ($message = error('name')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <label class="field">
        <span><?= e(__('usertypes.description')) ?></span>
        <input type="text" name="description" value="<?= e(old('description', $type?->description)) ?>" maxlength="255"
               <?= error('description') ? 'aria-invalid="true"' : '' ?>>
        <?php if ($message = error('description')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <fieldset class="permissions" <?= $locked ? 'disabled' : '' ?>>
        <legend><?= e(__('usertypes.permissions')) ?></legend>
        <?php if ($locked): ?>
            <p class="field__hint"><?= e(__('usertypes.locked_hint')) ?></p>
        <?php endif ?>

        <?php foreach (Permission::cases() as $permission): ?>
            <label class="check">
                <input type="checkbox" name="permissions[]" value="<?= e($permission) ?>"
                       <?= $locked || in_array($permission->value, $granted, true) ? 'checked' : '' ?>>
                <span>
                    <?= e($permission->label()) ?>
                    <small class="muted d-block"><code><?= e($permission->column()) ?></code></small>
                </span>
            </label>
        <?php endforeach ?>
    </fieldset>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.usertypes.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
