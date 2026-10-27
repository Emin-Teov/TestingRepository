<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\Product $product */
/** @var list<\App\Models\Category> $categories */
/** @var list<\App\Models\Unit> $units */
$editing = $product !== null;
$this->extend('layouts/admin', ['title' => $editing ? __('products.edit') : __('products.new')]);
$categoryId = (int) old('category_id', $product?->category_id);
$unitId = (int) old('unit_id', $product?->unit_id);
?>
<form class="card form form--narrow" method="post"
      action="<?= e($editing ? route('admin.products.update', ['id' => $product->id]) : route('admin.products.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('products.sku')) ?></span>
            <input type="text" name="sku" value="<?= e(old('sku', $product?->sku)) ?>" required maxlength="55" autofocus
                   <?= error('sku') ? 'aria-invalid="true"' : '' ?>>
            <small class="field__hint"><?= e(__('products.sku_hint')) ?></small>
            <?php if ($message = error('sku')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('units.unit')) ?></span>
            <select name="unit_id" required <?= error('unit_id') ? 'aria-invalid="true"' : '' ?>>
                <option value=""><?= e(__('common.choose')) ?></option>
                <?php foreach ($units as $unit): ?>
                    <option value="<?= e($unit->id) ?>" <?= $unit->id === $unitId ? 'selected' : '' ?>><?= e("{$unit->name} ({$unit->symbol})") ?></option>
                <?php endforeach ?>
            </select>
            <?php if ($message = error('unit_id')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>
    </div>

    <label class="field">
        <span><?= e(__('products.name')) ?></span>
        <input type="text" name="name" value="<?= e(old('name', $product?->name)) ?>" required minlength="2" maxlength="100"
               <?= error('name') ? 'aria-invalid="true"' : '' ?>>
        <?php if ($message = error('name')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <label class="field">
        <span><?= e(__('products.category')) ?></span>
        <select name="category_id" <?= error('category_id') ? 'aria-invalid="true"' : '' ?>>
            <option value="">—</option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category->id) ?>" <?= $category->id === $categoryId ? 'selected' : '' ?>><?= e($category->name) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($message = error('category_id')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <label class="field">
        <span><?= e(__('products.description')) ?></span>
        <textarea name="description" rows="3" maxlength="2000" <?= error('description') ? 'aria-invalid="true"' : '' ?>><?= e(old('description', $product?->description)) ?></textarea>
        <?php if ($message = error('description')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.products.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
