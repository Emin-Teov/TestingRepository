<?php /** @var string $search */ ?>
<form class="search" method="get" action="<?= e(url(request()->path)) ?>" role="search">
    <input type="search" name="q" value="<?= e($search) ?>" placeholder="<?= e(__('common.search')) ?>" aria-label="<?= e(__('common.search')) ?>" maxlength="100">
    <button type="submit" class="btn btn--ghost"><?= e(__('common.search')) ?></button>
</form>
