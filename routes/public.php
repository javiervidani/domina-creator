<?php

use App\Http\Controllers\EmbedController;
use Illuminate\Support\Facades\Route;

// Loaded without the web group (no session, no cookies, no CSRF): read-only pages for visitors on other sites.
// No closures here: the deploy runs route:cache, which cannot store them.
Route::middleware('throttle:120,1')->group(function () {
    Route::get('embed/{token}', [EmbedController::class, 'byToken']);
    Route::get('feed/{token}/json', [EmbedController::class, 'byToken'])->defaults('format', 'json');
    Route::get('feed/{token}/rss', [EmbedController::class, 'byToken'])->defaults('format', 'rss');
    Route::get('c/{creator}/{slug}', [EmbedController::class, 'bySlug'])->whereNumber('creator');
    Route::get('c/{creator}/{slug}/json', [EmbedController::class, 'bySlug'])->whereNumber('creator')->defaults('format', 'json');
    Route::get('c/{creator}/{slug}/rss', [EmbedController::class, 'bySlug'])->whereNumber('creator')->defaults('format', 'rss');
});
