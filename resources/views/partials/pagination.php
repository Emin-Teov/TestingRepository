<?php
/** @var \Framework\Database\Paginator $paginator */
$link = static fn (int $page) => url(request()->path, [...request()->queryAll(), 'page' => $page]);
?>
<?php if ($paginator->total > 0): ?>
<nav class="pagination" aria-label="Pagination">
    <span class="pagination__info">
        <?= e(__('pagination.showing', ['from' => $paginator->firstItem(), 'to' => $paginator->lastItem(), 'total' => $paginator->total])) ?>
    </span>

    <?php if ($paginator->hasPages()): ?>
        <div class="pagination__links">
            <?php if ($paginator->currentPage > 1): ?>
                <a class="btn btn--ghost" href="<?= e($link($paginator->currentPage - 1)) ?>" rel="prev"><?= e(__('pagination.previous')) ?></a>
            <?php endif ?>

            <?php for ($page = max(1, $paginator->currentPage - 2); $page <= min($paginator->lastPage(), $paginator->currentPage + 2); $page++): ?>
                <a class="btn <?= $page === $paginator->currentPage ? '' : 'btn--ghost' ?>" href="<?= e($link($page)) ?>" <?= $page === $paginator->currentPage ? 'aria-current="page"' : '' ?>><?= $page ?></a>
            <?php endfor ?>

            <?php if ($paginator->currentPage < $paginator->lastPage()): ?>
                <a class="btn btn--ghost" href="<?= e($link($paginator->currentPage + 1)) ?>" rel="next"><?= e(__('pagination.next')) ?></a>
            <?php endif ?>
        </div>
    <?php endif ?>
</nav>
<?php endif ?>
