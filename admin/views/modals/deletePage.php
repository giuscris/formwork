<div id="deletePageModal" class="modal">
    <div class="modal-content">
        <form action="" method="post">
            <h3 class="caption"><?= $this->translate('admin.pages.delete-page') ?></h3>
            <p class="modal-text"><?= $this->translate('admin.pages.delete-page.prompt') ?></p>
            <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">
            <button type="button" data-dismiss="deletePageModal"><?= $this->translate('admin.modal.action.cancel') ?></button>
            <button type="submit" class="button-error button-right" data-command="delete"><i class="i-trash"></i> <?= $this->translate('admin.modal.action.delete') ?></button>
        </form>
    </div>
</div>
