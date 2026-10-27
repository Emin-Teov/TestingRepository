<?php
/** @var \Framework\View\Template $this */
/** @var list<array{id: int, sku: string, name: string, category: ?string, unit: string, entries: int, quantity: float, value: float, average_price: float, last_entry: ?string}> $rows */
/** @var float $totalValue */
/** @var list<\App\Models\Category> $categories */
/** @var array{category_id: ?int, from: ?string, to: ?string} $filters */
use App\Support\Format;

$this->extend('layouts/admin', ['title' => __('reports.title')]);
?>
<form class="card filters" method="get" action="<?= e(route('admin.reports.stock')) ?>">
    <label class="field">
        <span><?= e(__('products.category')) ?></span>
        <select name="category_id">
            <option value=""><?= e(__('products.all_categories')) ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category->id) ?>" <?= $category->id === $filters['category_id'] ? 'selected' : '' ?>><?= e($category->name) ?></option>
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
    <div class="filters__actions">
        <button type="submit" class="btn"><?= e(__('common.filter')) ?></button>
        <?php if (array_filter($filters) !== []): ?>
            <a class="btn btn--ghost" href="<?= e(route('admin.reports.stock')) ?>"><?= e(__('common.reset')) ?></a>
        <?php endif ?>
        <button type="button" class="btn btn--ghost" data-print><?= e(__('common.print')) ?></button>
    </div>
</form>

<p class="muted"><?= e(__('reports.hint')) ?></p>

<?php if ($rows === []): ?>
    <div class="empty"><?= e(__('products.empty')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('products.sku')) ?></th>
                    <th><?= e(__('products.name')) ?></th>
                    <th><?= e(__('products.category')) ?></th>
                    <th class="num"><?= e(__('reports.entries')) ?></th>
                    <th class="num"><?= e(__('reports.quantity')) ?></th>
                    <th class="num"><?= e(__('reports.average_price')) ?></th>
                    <th class="num"><?= e(__('reports.value')) ?></th>
                    <th><?= e(__('reports.last_entry')) ?></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($rows as $row): ?>
                    <tr class="<?= $row['entries'] === 0 ? 'is-muted' : '' ?>">
                        <td data-label="<?= e(__('products.sku')) ?>" class="nowrap"><code><?= e($row['sku']) ?></code></td>
                        <td data-label="<?= e(__('products.name')) ?>"><strong><?= e($row['name']) ?></strong></td>
                        <td data-label="<?= e(__('products.category')) ?>"><?= e($row['category'] ?? '—') ?></td>
                        <td data-label="<?= e(__('reports.entries')) ?>" class="num"><?= e($row['entries']) ?></td>
                        <td data-label="<?= e(__('reports.quantity')) ?>" class="num nowrap"><?= e(Format::quantity($row['quantity'], $row['unit'])) ?></td>
                        <td data-label="<?= e(__('reports.average_price')) ?>" class="num nowrap"><?= e($row['entries'] > 0 ? Format::money($row['average_price']) . ' / ' . $row['unit'] : '—') ?></td>
                        <td data-label="<?= e(__('reports.value')) ?>" class="num nowrap"><strong><?= e(Format::money($row['value'])) ?></strong></td>
                        <td data-label="<?= e(__('reports.last_entry')) ?>" class="nowrap muted"><?= e($row['last_entry'] ?? '—') ?></td>
                    </tr>
                <?php endforeach ?>
            </tbody>
            <tfoot>
                <tr>
                    <th colspan="6"><?= e(__('reports.total')) ?></th>
                    <th class="num nowrap"><?= e(Format::money($totalValue)) ?></th>
                    <th></th>
                </tr>
            </tfoot>
        </table>
    </div>
<?php endif ?>
