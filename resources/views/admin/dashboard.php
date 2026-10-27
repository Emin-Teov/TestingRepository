<?php
/** @var \Framework\View\Template $this */
/** @var array{products: int, stock_value: float, entries_this_month: int, value_this_month: float, suppliers: int} $stats */
/** @var list<\App\Models\StockEntry> $recent */
/** @var array{products: array<int, \App\Models\Product>, units: array<int, \App\Models\Unit>, suppliers: array, users: array} $relations */
/** @var array<string, float> $activity */
use App\Enums\Permission;
use App\Support\Format;

$this->extend('layouts/admin', ['title' => __('dashboard.title')]);
$user = auth()->user();
$peak = max(1, ...array_values($activity));
?>
<section class="stats">
    <?php if ($user->can(Permission::ManageProducts)): ?>
        <a class="card stat" href="<?= e(route('admin.products.index')) ?>">
    <?php else: ?>
        <div class="card stat">
    <?php endif ?>
        <span class="stat__label"><?= e(__('dashboard.products')) ?></span>
        <strong class="stat__value"><?= e($stats['products']) ?></strong>
    <?= $user->can(Permission::ManageProducts) ? '</a>' : '</div>' ?>
    <div class="card stat">
        <span class="stat__label"><?= e(__('dashboard.stock_value')) ?></span>
        <strong class="stat__value stat__value--money"><?= e(Format::money($stats['stock_value'])) ?></strong>
    </div>
    <div class="card stat">
        <span class="stat__label"><?= e(__('dashboard.entries_this_month')) ?></span>
        <strong class="stat__value"><?= e($stats['entries_this_month']) ?></strong>
    </div>
    <div class="card stat">
        <span class="stat__label"><?= e(__('dashboard.value_this_month')) ?></span>
        <strong class="stat__value stat__value--money"><?= e(Format::money($stats['value_this_month'])) ?></strong>
    </div>
    <div class="card stat">
        <span class="stat__label"><?= e(__('dashboard.suppliers')) ?></span>
        <strong class="stat__value"><?= e($stats['suppliers']) ?></strong>
    </div>
</section>

<?php if ($user->can(Permission::CreateEntries)): ?>
    <div>
        <a class="btn" href="<?= e(route('admin.entries.create')) ?>">+ <?= e(__('entries.new')) ?></a>
    </div>
<?php endif ?>

<div class="grid-2">
    <section class="card">
        <h2 class="card__title"><?= e(__('dashboard.activity')) ?></h2>

        <div class="bar-chart" aria-hidden="true">
            <?php foreach ($activity as $day => $value): ?>
                <div class="bar-chart__col" data-tooltip="<?= e($day . ': ' . Format::money($value)) ?>">
                    <span class="bar-chart__value"><?= $value === $peak && $value > 0 ? e(Format::money($value, false)) : '' ?></span>
                    <span class="bar-chart__bar" style="height: <?= round($value / $peak * 100, 1) ?>%"></span>
                    <span class="bar-chart__label"><?= e(date('d.m', strtotime($day))) ?></span>
                </div>
            <?php endforeach ?>
        </div>

        <table class="visually-hidden">
            <caption><?= e(__('dashboard.activity')) ?></caption>
            <tbody>
                <?php foreach ($activity as $day => $value): ?>
                    <tr><th scope="row"><?= e($day) ?></th><td><?= e(Format::money($value)) ?></td></tr>
                <?php endforeach ?>
            </tbody>
        </table>
    </section>

    <?php if ($user->can(Permission::ViewEntries)): ?>
        <section class="card">
            <div class="card__head">
                <h2 class="card__title"><?= e(__('dashboard.recent')) ?></h2>
                <a href="<?= e(route('admin.entries.index')) ?>"><?= e(__('dashboard.view_all')) ?> →</a>
            </div>

            <?php if ($recent === []): ?>
                <p class="muted"><?= e(__('entries.empty')) ?></p>
            <?php else: ?>
                <ul class="list">
                    <?php foreach ($recent as $entry):
                        $product = $relations['products'][$entry->product_id] ?? null;
                        $unit = $relations['units'][$product?->unit_id ?? 0] ?? null; ?>
                        <li class="list__row">
                            <span>
                                <strong><?= e($product?->name ?? '—') ?></strong>
                                <span class="muted"><?= e(Format::quantity($entry->quantity, $unit?->symbol)) ?> · <?= e($entry->entry_date->format('Y-m-d')) ?></span>
                            </span>
                            <strong class="nowrap"><?= e(Format::money($entry->total_value)) ?></strong>
                        </li>
                    <?php endforeach ?>
                </ul>
            <?php endif ?>
        </section>
    <?php endif ?>
</div>
