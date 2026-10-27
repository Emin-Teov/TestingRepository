<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\Unit $unit */
$editing = $unit !== null;
$this->extend('layouts/admin', ['title' => $editing ? __('units.edit') : __('units.new')]);
?>
<form class="card form form--narrow" method="post"
      action="<?= e($editing ? route('admin.units.update', ['id' => $unit->id]) : route('admin.units.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('units.name')) ?></span>
            <input type="text" name="name" value="<?= e(old('name', $unit?->name)) ?>" required maxlength="55" autofocus placeholder="<?= e(__('units.name_example')) ?>"
                   <?= error('name') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('name')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('units.symbol')) ?></span>
            <input type="text" name="symbol" value="<?= e(old('symbol', $unit?->symbol)) ?>" required maxlength="15" placeholder="kg"
                   <?= error('symbol') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('symbol')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>
    </div>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.units.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
