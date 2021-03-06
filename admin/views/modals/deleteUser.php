<div id="deleteUserModal" class="modal">
    <div class="modal-content">
        <form action="" method="post">
            <h3 class="caption"><?= $this->translate('admin.users.delete-user') ?></h3>
            <p class="modal-text"><?= $this->translate('admin.users.delete-user.prompt') ?></p>
            <input type="hidden" name="csrf-token" value="<?= $csrfToken ?>">
            <button type="button" data-dismiss="deleteUserModal"><?= $this->translate('admin.modal.action.cancel') ?></button>
            <button type="submit" class="button-error button-right" data-command="delete"><i class="i-trash"></i> <?= $this->translate('admin.modal.action.delete') ?></button>
        </form>
    </div>
</div>
