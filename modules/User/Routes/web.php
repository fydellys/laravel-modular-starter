<?php

use Illuminate\Support\Facades\Route;
use Modules\User\Http\Controllers\ProfileController;

/*
| User module routes. Loaded by UserServiceProvider inside the "web"
| middleware group, so session/cookies/CSRF already apply here.
*/

Route::middleware(['auth'])->group(function () {
    Route::redirect('settings', '/settings/profile');

    Route::get('settings/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('profile.update');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::delete('settings/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});
