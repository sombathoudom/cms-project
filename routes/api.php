<?php

use App\Http\Controllers\Api\Admin\AuditLogController;
use App\Http\Controllers\Api\Admin\ContentController;
use App\Http\Controllers\Api\Admin\MediaController;
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

        Route::post('admin/content/{content}/media', [ContentController::class, 'embedMedia'])
            ->name('api.admin.content.embed-media');

        Route::get('admin/media', [MediaController::class, 'index'])
            ->name('api.admin.media.index');

        Route::post('admin/media', [MediaController::class, 'store'])
            ->name('api.admin.media.store');
    });
