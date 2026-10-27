<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $types */
/** @var string $search */
use App\Enums\Permission;

$this->extend('layouts/admin', ['title' => __('usertypes.title')]);
$total = count(Permission::cases());
?>
<div class="toolbar">
    <?= $this->insert('partials/search', ['search' => $search]) ?>
    <a class="btn" href="<?= e(route('admin.usertypes.create')) ?>">+ <?= e(__('usertypes.new')) ?></a>
</div>

<?php if ($types->isEmpty()): ?>
    <div class="empty"><?= e(__('common.no_matches')) ?></div>
<?php else: ?>
    <div class="card table-wrap">
        <table class="table">
            <thead>
                <tr>
                    <th><?= e(__('usertypes.name')) ?></th>
                    <th><?= e(__('usertypes.permissions')) ?></th>
                    <th class="num"><?= e(__('usertypes.users')) ?></th>
                    <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($types->items as $type): /** @var \App\Models\UserType $type */
                    $permissions = $type->permissions(); ?>
                    <tr>
                        <td data-label="<?= e(__('usertypes.name')) ?>">
                            <strong><?= e($type->name) ?></strong>
                            <?php if ($type->is_locked): ?><span class="badge badge--admin"><?= e(__('usertypes.built_in')) ?></span><?php endif ?>
                            <?php if ($type->description !== null): ?><small class="muted d-block"><?= e($type->description) ?></small><?php endif ?>
                        </td>
                        <td data-label="<?= e(__('usertypes.permissions')) ?>">
                            <?php if (count($permissions) === $total): ?>
                                <span class="badge badge--good"><?= e(__('usertypes.all_permissions')) ?></span>
                            <?php elseif ($permissions === []): ?>
                                <span class="muted"><?= e(__('usertypes.no_permissions')) ?></span>
                            <?php else: ?>
                                <div class="tags">
                                    <?php foreach ($permissions as $permission): ?>
                                        <span class="badge"><?= e($permission->label()) ?></span>
                                    <?php endforeach ?>
                                </div>
                            <?php endif ?>
                        </td>
                        <td data-label="<?= e(__('usertypes.users')) ?>" class="num"><?= e($type->usersCount()) ?></td>
                        <td class="table__actions">
                            <a class="btn btn--small btn--ghost" href="<?= e(route('admin.usertypes.edit', ['id' => $type->id])) ?>"><?= e(__('common.edit')) ?></a>
                            <?php if (!$type->is_locked): ?>
                                <form method="post" action="<?= e(route('admin.usertypes.destroy', ['id' => $type->id])) ?>" data-confirm="<?= e(__('usertypes.delete_confirm')) ?>">
                                    <?= csrf_field() ?>
                                    <?= method_field('DELETE') ?>
                                    <button type="submit" class="btn btn--small btn--danger"><?= e(__('common.delete')) ?></button>
                                </form>
                            <?php endif ?>
                        </td>
                    </tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </div>

    <?= $this->insert('partials/pagination', ['paginator' => $types]) ?>
<?php endif ?>
