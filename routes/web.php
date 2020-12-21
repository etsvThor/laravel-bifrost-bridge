<?php

use Illuminate\Support\Facades\Route;
use EtsvThor\BifrostBridge\Http\Controllers\LoginController;
use EtsvThor\BifrostBridge\Http\Controllers\WebhookController;

Route::get('login', [LoginController::class, 'redirect'])->name('login');
Route::get('login/callback', [LoginController::class, 'callback']);
Route::post('logout', [LoginController::class, 'logout'])->name('logout');

if (! is_null(config('bifrost.auth_push_key'))) {
    Route::post('webhooks/bifrost', [WebhookController::class, 'bifrost'])->name('webhooks.bifrost');
}