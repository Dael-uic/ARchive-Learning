<?php

use Illuminate\Support\Facades\Route;

// The actual UI is the static frontend in /public (index.html, viewer.html, etc.)
// — identical to the tested prototype in /web, just pointed at Laravel's API
// and storage disk instead of FastAPI's. This route only exists so visiting
// the bare domain lands somewhere useful.
Route::get('/', fn () => redirect('/login.html'));
