<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $products */
/** @var array<int, float> $quantities */
/** @var array<int, \App\Models\Unit> $units */
/** @var list<\App\Models\Category> $categories */
/** @var string $search */
/** @var int $categoryId */
use App\Enums\Permission;
use App\Support\Format;

$this->extend('layouts/admin', ['title' => __('products.title')]);
/** @var \App\Models\User $user */
$user = auth()->user();
$categoryNames = array_column(array_map(static fn ($category) => [$category->id, $category->name], $categories), 1, 0);
?>
<div class="toolbar">
    <form class="search" method="get" action="<?= e(route('admin.products.index')) ?>" role="search">
        <select name="category_id" aria-label="<?= e(__('products.category')) ?>">
            <option value=""><?= e(__('products.all_categories')) ?></option>
            <?php foreach ($categories as $category): ?>
                <option value="<?= e($category->id) ?>" <?= $category->id === $categoryId ? 'selected' : '' ?>><?= e($category->name) ?></option>
            <?php endforeach ?>
        </select>
        <input type="search" name="q" value="<?= e($search) ?>" placeholder="<?= e(__('common.search')) ?>" aria-label="<?= e(__('common.search')) ?>" maxlength="100">
        <button type="submit" class="btn btn--ghost"><?= e(__('common.search')) ?></button>
    </form>
    <a class="btn" href="<?= e(route('admin.products.create')) ?>">+ <?= e(__('products.new')) ?></a>
</div>

<?php if ($products->isEmpty()): ?>
    <div class="empty"><?= e($search !== '' || $categoryId ? __('common.no_matches') : __('products.empty')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('products.sku')) ?></th>
                    <th><?= e(__('products.name')) ?></th>
                    <th><?= e(__('products.category')) ?></th>
                    <th><?= e(__('units.unit')) ?></th>
                    <th class="num"><?= e(__('products.received')) ?></th>
                    <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($products->items as $product): /** @var \App\Models\Product $product */
                    $unit = $units[$product->unit_id] ?? null; ?>
                    <tr>
                        <td data-label="<?= e(__('products.sku')) ?>" class="nowrap"><code><?= e($product->sku) ?></code></td>
                        <td data-label="<?= e(__('products.name')) ?>">
                            <strong><?= e($product->name) ?></strong>
                            <?php if ($product->description !== null): ?><small class="muted d-block"><?= e(\Framework\Support\Str::limit($product->description, 70)) ?></small><?php endif ?>
                        </td>
                        <td data-label="<?= e(__('products.category')) ?>"><?= e($categoryNames[$product->category_id ?? 0] ?? '—') ?></td>
                        <td data-label="<?= e(__('units.unit')) ?>"><?= e($unit ? "{$unit->name} ({$unit->symbol})" : '—') ?></td>
                        <td data-label="<?= e(__('products.received')) ?>" class="num nowrap"><?= e(Format::quantity($quantities[$product->id] ?? 0.0, $unit?->symbol)) ?></td>
                        <td class="table__actions">
                            <?php if ($user->can(Permission::CreateEntries)): ?>
                                <a class="btn btn--small" href="<?= e(route('admin.entries.create', [], ['product_id' => $product->id])) ?>">+ <?= e(__('products.receive')) ?></a>
                            <?php endif ?>
                            <?php if ($user->can(Permission::ViewEntries)): ?>
                                <a class="btn btn--small btn--ghost" href="<?= e(route('admin.entries.index', [], ['product_id' => $product->id])) ?>"><?= e(__('products.history')) ?></a>
                            <?php endif ?>
                            <a class="btn btn--small btn--ghost" href="<?= e(route('admin.products.edit', ['id' => $product->id])) ?>"><?= e(__('common.edit')) ?></a>
                            <form method="post" action="<?= e(route('admin.products.destroy', ['id' => $product->id])) ?>" data-confirm="<?= e(__('products.delete_confirm')) ?>">
                                <?= csrf_field() ?>
                                <?= method_field('DELETE') ?>
                                <button type="submit" class="btn btn--small btn--danger"><?= e(__('common.delete')) ?></button>
                            </form>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?= $this->insert('partials/pagination', ['paginator' => $products]) ?>
<?php endif ?>
