<?php
/** @var \Framework\View\Template $this */
/** @var ?\App\Models\StockEntry $entry */
/** @var ?int $selectedProduct */
/** @var list<\App\Models\Product> $products */
/** @var array<int, \App\Models\Unit> $units */
/** @var list<\App\Models\Supplier> $suppliers */
$editing = $entry !== null;
$this->extend('layouts/admin', ['title' => $editing ? __('entries.edit') : __('entries.new')]);
$productId = (int) old('product_id', $selectedProduct);
$supplierId = (int) old('supplier_id', $entry?->supplier_id);
// Show stored decimals without trailing zeros ("1500" rather than "1500.000").
$number = static fn (?float $value) => $value === null ? '' : rtrim(rtrim(number_format($value, 3, '.', ''), '0'), '.');
?>
<form class="card form form--narrow" method="post" data-entry-form data-currency="<?= e(config('app.currency')) ?>"
      action="<?= e($editing ? route('admin.entries.update', ['id' => $entry->id]) : route('admin.entries.store')) ?>">
    <?= csrf_field() ?>
    <?php if ($editing): ?><?= method_field('PUT') ?><?php endif ?>

    <?php if ($products === []): ?>
        <div class="alert alert--warning"><span><?= e(__('entries.no_products')) ?></span></div>
    <?php endif ?>

    <label class="field">
        <span><?= e(__('entries.product')) ?></span>
        <select name="product_id" required autofocus <?= error('product_id') ? 'aria-invalid="true"' : '' ?>>
            <option value=""><?= e(__('common.choose')) ?></option>
            <?php foreach ($products as $product): ?>
                <option value="<?= e($product->id) ?>" data-unit="<?= e($units[$product->unit_id]->symbol ?? '') ?>" <?= $product->id === $productId ? 'selected' : '' ?>>
                    <?= e($product->name) ?> (<?= e($product->sku) ?>)
                </option>
            <?php endforeach ?>
        </select>
        <?php if ($message = error('product_id')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('entries.quantity')) ?> <span class="muted" data-unit-label></span></span>
            <input type="number" name="quantity" value="<?= e(old('quantity', $number($entry?->quantity))) ?>" required min="0.001" max="99999999" step="0.001"
                   inputmode="decimal" <?= error('quantity') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('quantity')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('entries.unit_price')) ?> (<?= e(config('app.currency')) ?>)</span>
            <input type="number" name="unit_price" value="<?= e(old('unit_price', $entry ? number_format($entry->unit_price, 2, '.', '') : '')) ?>" required min="0" max="99999999" step="0.01"
                   inputmode="decimal" <?= error('unit_price') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('unit_price')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>
    </div>

    <p class="total-preview"><?= e(__('entries.total_value')) ?>: <strong data-total>—</strong></p>

    <div class="form__row">
        <label class="field">
            <span><?= e(__('entries.date')) ?></span>
            <input type="date" name="entry_date" value="<?= e(old('entry_date', $entry?->entry_date->format('Y-m-d') ?? date('Y-m-d'))) ?>" required
                   <?= error('entry_date') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('entry_date')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>

        <label class="field">
            <span><?= e(__('entries.document_no')) ?></span>
            <input type="text" name="document_no" value="<?= e(old('document_no', $entry?->document_no)) ?>" maxlength="55"
                   <?= error('document_no') ? 'aria-invalid="true"' : '' ?>>
            <?php if ($message = error('document_no')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
        </label>
    </div>

    <label class="field">
        <span><?= e(__('entries.supplier')) ?></span>
        <select name="supplier_id" <?= error('supplier_id') ? 'aria-invalid="true"' : '' ?>>
            <option value="">—</option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= e($supplier->id) ?>" <?= $supplier->id === $supplierId ? 'selected' : '' ?>><?= e($supplier->name) ?></option>
            <?php endforeach ?>
        </select>
        <?php if ($message = error('supplier_id')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <label class="field">
        <span><?= e(__('entries.note')) ?></span>
        <textarea name="note" rows="2" maxlength="255" <?= error('note') ? 'aria-invalid="true"' : '' ?>><?= e(old('note', $entry?->note)) ?></textarea>
        <?php if ($message = error('note')): ?><small class="field__error"><?= e($message) ?></small><?php endif ?>
    </label>

    <div class="form__actions">
        <a class="btn btn--ghost" href="<?= e(route('admin.entries.index')) ?>"><?= e(__('common.cancel')) ?></a>
        <button type="submit" class="btn"><?= e(__('common.save')) ?></button>
    </div>
</form>
