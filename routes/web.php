<?php

use Illuminate\Support\Facades\Route;

// Panel host: landing (Phase 1). Auth and panel routes land here in 1.7/1.8;
// the short-host redirect routes arrive in 1.6 with their own minimal stack.
Route::middleware('setlocale')->group(function () {
    Route::get('/', fn () => view('welcome'))->name('landing');
});
