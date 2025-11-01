<?php

use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/health', \App\Http\Controllers\HealthCheckController::class);

Route::middleware('signed')
    ->get('/preview/content/{content}/{token}', \App\Http\Controllers\ContentPreviewController::class)
    ->name('content.preview');
