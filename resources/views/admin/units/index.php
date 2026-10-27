<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $units */
/** @var string $search */
$this->extend('layouts/admin', ['title' => __('units.title')]);
?>
<div class="toolbar">
    <?= $this->insert('partials/search', ['search' => $search]) ?>
    <a class="btn" href="<?= e(route('admin.units.create')) ?>">+ <?= e(__('units.new')) ?></a>
</div>

<?php if ($units->isEmpty()): ?>
    <div class="empty"><?= e($search !== '' ? __('common.no_matches') : __('units.empty')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('units.name')) ?></th>
                    <th><?= e(__('units.symbol')) ?></th>
                    <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($units->items as $unit): /** @var \App\Models\Unit $unit */ ?>
                    <tr>
                        <td data-label="<?= e(__('units.name')) ?>"><strong><?= e($unit->name) ?></strong></td>
                        <td data-label="<?= e(__('units.symbol')) ?>"><code><?= e($unit->symbol) ?></code></td>
                        <td class="table__actions">
                            <a class="btn btn--small btn--ghost" href="<?= e(route('admin.units.edit', ['id' => $unit->id])) ?>"><?= e(__('common.edit')) ?></a>
                            <form method="post" action="<?= e(route('admin.units.destroy', ['id' => $unit->id])) ?>" data-confirm="<?= e(__('units.delete_confirm')) ?>">
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

    <?= $this->insert('partials/pagination', ['paginator' => $units]) ?>
<?php endif ?>
