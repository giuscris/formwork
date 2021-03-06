<?php

namespace Formwork\Admin\Controllers;

use Formwork\Admin\Backupper;
use Formwork\Admin\Updater;
use Formwork\Exceptions\TranslatedException;
use Formwork\Formwork;
use Formwork\Response\JSONResponse;
use RuntimeException;

class UpdatesController extends AbstractController
{
    /**
     * Updates@check action
     */
    public function check(): JSONResponse
    {
        $this->ensurePermission('updates.check');
        $updater = new Updater(['preferDistAssets' => true]);
        try {
            $upToDate = $updater->checkUpdates();
        } catch (RuntimeException $e) {
            return JSONResponse::error($this->admin()->translate('admin.updates.status.cannot-check'), 500, [
                'status' => $this->admin()->translate('admin.updates.status.cannot-check')
            ]);
        }
        if ($upToDate) {
            return JSONResponse::success($this->admin()->translate('admin.updates.status.up-to-date'), 200, [
                'uptodate' => true
            ]);
        }
        return JSONResponse::success($this->admin()->translate('admin.updates.status.found'), 200, [
            'uptodate' => false,
            'release'  => $updater->latestRelease()
        ]);
    }

    /**
     * Updates@update action
     */
    public function update(): JSONResponse
    {
        $this->ensurePermission('updates.update');
        $updater = new Updater(['force' => true, 'preferDistAssets' => true, 'cleanupAfterInstall' => true]);
        if (Formwork::instance()->config()->get('updates.backup_before')) {
            $backupper = new Backupper();
            try {
                $backupper->backup();
            } catch (TranslatedException $e) {
                return JSONResponse::error($this->admin()->translate('admin.updates.status.cannot-make-backup'), 500, [
                    'status' => $this->admin()->translate('admin.updates.status.cannot-make-backup')
                ]);
            }
        }
        try {
            $updater->update();
        } catch (RuntimeException $e) {
            return JSONResponse::error($this->admin()->translate('admin.updates.status.cannot-install'), 500, [
                'status' => $this->admin()->translate('admin.updates.status.cannot-install')
            ]);
        }
        if (Formwork::instance()->config()->get('cache.enabled')) {
            Formwork::instance()->cache()->clear();
        }
        return JSONResponse::success($this->admin()->translate('admin.updates.installed'), 200, [
            'status' => $this->admin()->translate('admin.updates.status.up-to-date')
        ]);
    }
}
