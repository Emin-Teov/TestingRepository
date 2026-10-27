<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\Supplier $supplier */
$editing = $supplier !== null;
$this->extend('layouts/admin', ['title' => $editing ? __('suppliers.edit') : __('suppliers.new')]);
?>
<form class="card form form--narrow" method="post"
      action="<?= e($editing ? route('admin.suppliers.update', ['id' => $supplier->id]) : route('admin.suppliers.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <label class="field">
        <span><?= e(__('suppliers.name')) ?></span>
        <input type="text" name="name" value="<?= e(old('name', $supplier?->name)) ?>" required minlength="2" maxlength="100" autofocus
               <?= error('name') ? 'aria-invalid="true"' : '' ?>>
        <?php if ($message = error('name')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('suppliers.phone')) ?></span>
            <input type="tel" name="phone" value="<?= e(old('phone', $supplier?->phone)) ?>" maxlength="30"
                   <?= error('phone') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('phone')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('suppliers.email')) ?></span>
            <input type="email" name="email" value="<?= e(old('email', $supplier?->email)) ?>" maxlength="100"
                   <?= error('email') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('email')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>
    </div>

    <label class="field">
        <span><?= e(__('suppliers.address')) ?></span>
        <input type="text" name="address" value="<?= e(old('address', $supplier?->address)) ?>" maxlength="255"
               <?= error('address') ? 'aria-invalid="true"' : '' ?>>
        <?php if ($message = error('address')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.suppliers.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
