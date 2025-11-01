<?php

use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\ContentController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')
    ->middleware(['web', 'auth'])
    ->group(function (): void {
        Route::get('admin/audit-logs', [AuditLogController::class, 'index'])
            ->name('api.admin.audit-logs.index');

        Route::post('admin/content/{content}/autosave', [ContentController::class, 'autoSave'])
            ->name('api.admin.content.autosave');

        Route::post('admin/content/{content}/preview-link', [ContentController::class, 'previewLink'])
            ->name('api.admin.content.preview-link');
    });
