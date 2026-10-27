<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\Category $category */
$editing = $category !== null;
$this->extend('layouts/admin', ['title' => $editing ? __('categories.edit') : __('categories.new')]);
?>
<form class="card form form--narrow" method="post"
      action="<?= e($editing ? route('admin.categories.update', ['id' => $category->id]) : route('admin.categories.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <label class="field">
        <span><?= e(__('categories.name')) ?></span>
        <input type="text" name="name" value="<?= e(old('name', $category?->name)) ?>" required minlength="2" maxlength="100" autofocus
               <?= error('name') ? 'aria-invalid="true"' : '' ?>>
        <?php if ($message = error('name')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.categories.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
