<?php
$messages = array_filter([
    'success' => session()->getFlash('success'),
    'warning' => session()->getFlash('warning'),
    'error' => session()->getFlash('error'),
]);
?>
<?php foreach ($messages as $type => $message): ?>
    <div class="alert alert--<?= e($type) ?>" role="status">
        <span><?= e($message) ?></span>
        <button type="button" class="alert__close" data-dismiss aria-label="<?= e(__('common.close')) ?>">×</button>
    </div>
<?php endforeach ?>
