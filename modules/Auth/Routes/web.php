<?php

use Illuminate\Auth\Middleware\RequirePassword;
use Illuminate\Support\Facades\Route;
use Modules\Auth\Http\Controllers\SecurityController;

/*
| Auth module routes. Loaded by AuthServiceProvider inside the "web"
| middleware group. Login/registration/2FA routes themselves are provided
| by Fortify; these are the app-owned security settings screens.
*/

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('settings/security', [SecurityController::class, 'edit'])
        ->middleware(RequirePassword::class)
        ->name('security.edit');

    Route::put('settings/password', [SecurityController::class, 'update'])
        ->middleware('throttle:6,1')
        ->name('user-password.update');
});
