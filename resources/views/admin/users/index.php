<?php
/** @var \Framework\View\Template $this */
/** @var \Framework\Database\Paginator $users */
/** @var array<int, \App\Models\UserType> $types */
/** @var string $search */
$this->extend('layouts/admin', ['title' => __('users.title')]);
$currentId = auth()->id();
?>
<div class="toolbar">
    <?= $this->insert('partials/search', ['search' => $search]) ?>
    <a class="btn" href="<?= e(route('admin.users.create')) ?>">+ <?= e(__('users.new')) ?></a>
</div>

<div class="card table-wrap">
    <table class="table">
        <thead>
            <tr>
                <th><?= e(__('users.username')) ?></th>
                <th><?= e(__('users.full_name')) ?></th>
                <th><?= e(__('users.usertype')) ?></th>
                <th><?= e(__('users.status')) ?></th>
                <th><?= e(__('common.created_at')) ?></th>
                <th class="table__actions"><span class="visually-hidden"><?= e(__('common.actions')) ?></span></th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($users->items as $user): /** @var \App\Models\User $user */
                $type = $types[$user->usertype_id] ?? null; ?>
                <tr>
                    <td data-label="<?= e(__('users.username')) ?>">
                        <strong><?= e($user->username) ?></strong>
                        <?php if ($user->id === $currentId): ?><span class="muted">(<?= e(__('users.you')) ?>)</span><?php endif ?>
                    </td>
                    <td data-label="<?= e(__('users.full_name')) ?>"><?= e($user->full_name ?? '—') ?></td>
                    <td data-label="<?= e(__('users.usertype')) ?>">
                        <span class="badge <?= $type?->is_locked ? 'badge--admin' : '' ?>"><?= e($type?->name ?? '—') ?></span>
                    </td>
                    <td data-label="<?= e(__('users.status')) ?>">
                        <?php if ($user->is_active): ?>
                            <span class="badge badge--good"><?= e(__('users.active')) ?></span>
                        <?php else: ?>
                            <span class="badge badge--warning"><?= e(__('users.inactive')) ?></span>
                        <?php endif ?>
                    </td>
                    <td data-label="<?= e(__('common.created_at')) ?>" class="nowrap muted"><?= e($user->created_at?->format('Y-m-d H:i') ?? '—') ?></td>
                    <td class="table__actions">
                        <a class="btn btn--small btn--ghost" href="<?= e(route('admin.users.edit', ['id' => $user->id])) ?>"><?= e(__('common.edit')) ?></a>
                        <?php if ($user->id !== $currentId): ?>
                            <form method="post" action="<?= e(route('admin.users.destroy', ['id' => $user->id])) ?>" data-confirm="<?= e(__('users.delete_confirm')) ?>">
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

<?= $this->insert('partials/pagination', ['paginator' => $users]) ?>
