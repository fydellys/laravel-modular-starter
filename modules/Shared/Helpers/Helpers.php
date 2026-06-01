<?php

use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\Auth;

if (! function_exists('user')) {
    /**
     * Get the currently authenticated user.
     */
    function user(): ?Authenticatable
    {
        return Auth::user();
    }
}
