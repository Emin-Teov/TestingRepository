<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $suppliers */
/** @var string $search */
use App\Enums\Permission;

$this->extend('layouts/admin', ['title' => __('suppliers.title')]);
/** @var \App\Models\User $user */
$user = auth()->user();
?>
<div class="toolbar">
    <?= $this->insert('partials/search', ['search' => $search]) ?>
    <a class="btn" href="<?= e(route('admin.suppliers.create')) ?>">+ <?= e(__('suppliers.new')) ?></a>
</div>

<?php if ($suppliers->isEmpty()): ?>
    <div class="empty"><?= e($search !== '' ? __('common.no_matches') : __('suppliers.empty')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('suppliers.name')) ?></th>
                    <th><?= e(__('suppliers.phone')) ?></th>
                    <th><?= e(__('suppliers.email')) ?></th>
                    <th><?= e(__('suppliers.address')) ?></th>
                    <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($suppliers->items as $supplier): /** @var \App\Models\Supplier $supplier */ ?>
                    <tr>
                        <td data-label="<?= e(__('suppliers.name')) ?>"><strong><?= e($supplier->name) ?></strong></td>
                        <td data-label="<?= e(__('suppliers.phone')) ?>" class="nowrap">
                            <?php if ($supplier->phone !== null): ?><a href="tel:<?= e($supplier->phone) ?>"><?= e($supplier->phone) ?></a><?php else: ?>—<?php endif ?>
                        </td>
                        <td data-label="<?= e(__('suppliers.email')) ?>">
                            <?php if ($supplier->email !== null): ?><a href="mailto:<?= e($supplier->email) ?>"><?= e($supplier->email) ?></a><?php else: ?>—<?php endif ?>
                        </td>
                        <td data-label="<?= e(__('suppliers.address')) ?>"><?= e($supplier->address ?? '—') ?></td>
                        <td class="table__actions">
                            <?php if ($user->can(Permission::ViewEntries)): ?>
                                <a class="btn btn--small btn--ghost" href="<?= e(route('admin.entries.index', [], ['supplier_id' => $supplier->id])) ?>"><?= e(__('suppliers.deliveries')) ?></a>
                            <?php endif ?>
                            <a class="btn btn--small btn--ghost" href="<?= e(route('admin.suppliers.edit', ['id' => $supplier->id])) ?>"><?= e(__('common.edit')) ?></a>
                            <form method="post" action="<?= e(route('admin.suppliers.destroy', ['id' => $supplier->id])) ?>" data-confirm="<?= e(__('suppliers.delete_confirm')) ?>">
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

    <?= $this->insert('partials/pagination', ['paginator' => $suppliers]) ?>
<?php endif ?>
