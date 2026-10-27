<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $entries */
/** @var array{products: array<int, \App\Models\Product>, units: array<int, \App\Models\Unit>, suppliers: array<int, \App\Models\Supplier>, users: array<int, \App\Models\User>} $relations */
/** @var float $filteredValue */
/** @var list<\App\Models\Product> $products */
/** @var list<\App\Models\Supplier> $suppliers */
/** @var array{q: string, product_id: int, supplier_id: int, from: string, to: string} $filters */
use App\Enums\Permission;
use App\Support\Format;

$this->extend('layouts/admin', ['title' => __('entries.title')]);
$user = auth()->user();
$canEdit = $user->can(Permission::EditEntries);
$canDelete = $user->can(Permission::DeleteEntries);
$filtered = array_filter($filters) !== [];
?>
<form class="card filters" method="get" action="<?= e(route('admin.entries.index')) ?>" role="search">
    <label class="field">
        <span><?= e(__('entries.product')) ?></span>
        <select name="product_id">
            <option value=""><?= e(__('common.all')) ?></option>
            <?php foreach ($products as $product): ?>
                <option value="<?= e($product->id) ?>" <?= $product->id === $filters['product_id'] ? 'selected' : '' ?>><?= e($product->name) ?> (<?= e($product->sku) ?>)</option>
            <?php endforeach ?>
        </select>
    </label>
    <label class="field">
        <span><?= e(__('entries.supplier')) ?></span>
        <select name="supplier_id">
            <option value=""><?= e(__('common.all')) ?></option>
            <?php foreach ($suppliers as $supplier): ?>
                <option value="<?= e($supplier->id) ?>" <?= $supplier->id === $filters['supplier_id'] ? 'selected' : '' ?>><?= e($supplier->name) ?></option>
            <?php endforeach ?>
        </select>
    </label>
    <label class="field">
        <span><?= e(__('common.from')) ?></span>
        <input type="date" name="from" value="<?= e($filters['from']) ?>">
    </label>
    <label class="field">
        <span><?= e(__('common.to')) ?></span>
        <input type="date" name="to" value="<?= e($filters['to']) ?>">
    </label>
    <label class="field">
        <span><?= e(__('entries.document_no')) ?> / <?= e(__('entries.note')) ?></span>
        <input type="search" name="q" value="<?= e($filters['q']) ?>" maxlength="100">
    </label>
    <div class="filters__actions">
        <button type="submit" class="btn"><?= e(__('common.filter')) ?></button>
        <?php if ($filtered): ?>
            <a class="btn btn--ghost" href="<?= e(route('admin.entries.index')) ?>"><?= e(__('common.reset')) ?></a>
        <?php endif ?>
    </div>
</form>

<div class="toolbar">
    <p class="muted">
        <?= e(__('entries.total_for_filter')) ?>: <strong class="text"><?= e(Format::money($filteredValue)) ?></strong>
    </p>
    <div class="toolbar__buttons">
        <a class="btn btn--ghost" href="<?= e(route('admin.entries.export', [], array_filter($filters))) ?>"><?= e(__('common.export_csv')) ?></a>
        <?php if ($user->can(Permission::CreateEntries)): ?>
            <a class="btn" href="<?= e(route('admin.entries.create')) ?>">+ <?= e(__('entries.new')) ?></a>
        <?php endif ?>
    </div>
</div>

<?php if ($entries->isEmpty()): ?>
    <div class="empty"><?= e($filtered ? __('common.no_matches') : __('entries.empty')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('entries.date')) ?></th>
                    <th><?= e(__('entries.product')) ?></th>
                    <th><?= e(__('entries.supplier')) ?></th>
                    <th class="num"><?= e(__('entries.quantity')) ?></th>
                    <th class="num"><?= e(__('entries.unit_price')) ?></th>
                    <th class="num"><?= e(__('entries.total_value')) ?></th>
                    <th><?= e(__('entries.document_no')) ?></th>
                    <th><?= e(__('entries.recorded_by')) ?></th>
                    <?php if ($canEdit || $canDelete): ?>
                        <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
                    <?php endif ?>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($entries->items as $entry): /** @var \App\Models\StockEntry $entry */
                    $product = $relations['products'][$entry->product_id] ?? null;
                    $unit = $relations['units'][$product?->unit_id ?? 0] ?? null; ?>
                    <tr>
                        <td data-label="<?= e(__('entries.date')) ?>" class="nowrap"><?= e($entry->entry_date->format('Y-m-d')) ?></td>
                        <td data-label="<?= e(__('entries.product')) ?>">
                            <strong><?= e($product?->name ?? '—') ?></strong>
                            <small class="muted d-block"><?= e($product?->sku) ?></small>
                        </td>
                        <td data-label="<?= e(__('entries.supplier')) ?>"><?= e($relations['suppliers'][$entry->supplier_id ?? 0]->name ?? '—') ?></td>
                        <td data-label="<?= e(__('entries.quantity')) ?>" class="num nowrap"><?= e(Format::quantity($entry->quantity, $unit?->symbol)) ?></td>
                        <td data-label="<?= e(__('entries.unit_price')) ?>" class="num nowrap"><?= e(Format::money($entry->unit_price)) ?></td>
                        <td data-label="<?= e(__('entries.total_value')) ?>" class="num nowrap"><strong><?= e(Format::money($entry->total_value)) ?></strong></td>
                        <td data-label="<?= e(__('entries.document_no')) ?>">
                            <?= e($entry->document_no ?? '—') ?>
                            <?php if ($entry->note !== null): ?><small class="muted d-block"><?= e(\Framework\Support\Str::limit($entry->note, 60)) ?></small><?php endif ?>
                        </td>
                        <td data-label="<?= e(__('entries.recorded_by')) ?>" class="muted"><?= e($relations['users'][$entry->user_id ?? 0]->username ?? '—') ?></td>
                        <?php if ($canEdit || $canDelete): ?>
                            <td class="table__actions">
                                <?php if ($canEdit): ?>
                                    <a class="btn btn--small btn--ghost" href="<?= e(route('admin.entries.edit', ['id' => $entry->id])) ?>"><?= e(__('common.edit')) ?></a>
                                <?php endif ?>
                                <?php if ($canDelete): ?>
                                    <form method="post" action="<?= e(route('admin.entries.destroy', ['id' => $entry->id])) ?>" data-confirm="<?= e(__('entries.delete_confirm')) ?>">
                                        <?= csrf_field() ?>
                                        <?= method_field('DELETE') ?>
                                        <button type="submit" class="btn btn--small btn--danger"><?= e(__('common.delete')) ?></button>
                                    </form>
                                <?php endif ?>
                            </td>
                        <?php endif ?>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?= $this->insert('partials/pagination', ['paginator' => $entries]) ?>
<?php endif ?>
