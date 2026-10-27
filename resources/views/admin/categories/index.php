<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $categories */
/** @var string $search */
$this->extend('layouts/admin', ['title' => __('categories.title')]);
?>
<div class="toolbar">
    <?= $this->insert('partials/search', ['search' => $search]) ?>
    <a class="btn" href="<?= e(route('admin.categories.create')) ?>">+ <?= e(__('categories.new')) ?></a>
</div>

<?php if ($categories->isEmpty()): ?>
    <div class="empty"><?= e($search !== '' ? __('common.no_matches') : __('categories.empty')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('categories.name')) ?></th>
                    <th><?= e(__('common.created_at')) ?></th>
                    <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($categories->items as $category): /** @var \App\Models\Category $category */ ?>
                    <tr>
                        <td data-label="<?= e(__('categories.name')) ?>">
                            <a href="<?= e(route('admin.products.index', [], ['category_id' => $category->id])) ?>"><strong><?= e($category->name) ?></strong></a>
                        </td>
                        <td data-label="<?= e(__('common.created_at')) ?>" class="nowrap muted"><?= e($category->created_at?->format('Y-m-d H:i') ?? '—') ?></td>
                        <td class="table__actions">
                            <a class="btn btn--small btn--ghost" href="<?= e(route('admin.categories.edit', ['id' => $category->id])) ?>"><?= e(__('common.edit')) ?></a>
                            <form method="post" action="<?= e(route('admin.categories.destroy', ['id' => $category->id])) ?>" data-confirm="<?= e(__('categories.delete_confirm')) ?>">
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

    <?= $this->insert('partials/pagination', ['paginator' => $categories]) ?>
<?php endif ?>
