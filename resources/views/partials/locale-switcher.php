<?php
$active = app(\Framework\Translation\Translator::class)->getLocale();
?>
<div class="locales" aria-label="<?= e(__('nav.language')) ?>">
    <?php foreach (config('app.locales') as $code => $name): ?>
        <form method="post" action="<?= e(route('locale', ['locale' => $code])) ?>">
            <?= csrf_field() ?>
            <button type="submit" class="locale <?= $code === $active ? 'is-active' : '' ?>" title="<?= e($name) ?>" <?= $code === $active ? 'aria-current="true"' : '' ?>>
                <img src="<?= e(asset("assets/flags/$code.png")) ?>" alt="<?= e($name) ?>" width="22" height="22">
            </button>
        </form>
    <?php endforeach ?>
</div>
