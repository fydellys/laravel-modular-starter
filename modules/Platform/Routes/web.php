<?php

use Illuminate\Support\Facades\Route;

/*
| Platform (app shell) routes. Loaded by PlatformServiceProvider inside the
| "web" middleware group. These are the host application's own surfaces —
| the welcome page, the dashboard frame, the appearance toggle — not owned by
| any domain (bounded-context) module.
*/

Route::inertia('/', 'Welcome')->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // Generic UI preference — neither a User nor an Auth domain concern.
    Route::inertia('settings/appearance', 'settings/Appearance')->name('appearance.edit');
});
