<dialog class="dialog" id="confirm-dialog">
    <form method="dialog">
        <p class="dialog__message" data-message></p>
        <div class="dialog__actions">
            <button value="cancel" class="btn btn--ghost"><?= e(__('common.no')) ?></button>
            <button value="confirm" class="btn btn--danger"><?= e(__('common.yes')) ?></button>
        </div>
    </form>
</dialog>
