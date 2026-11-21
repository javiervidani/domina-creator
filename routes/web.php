<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AiKeyController;
use App\Http\Controllers\AccountTokenController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CollectionItemsController;
use App\Http\Controllers\CollectionsController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeletionController;
use App\Http\Controllers\ContentPartsController;
use App\Http\Controllers\ContentSourceController;
use App\Http\Controllers\ContentTargetController;
use App\Http\Controllers\EmbedSettingsController;
use App\Http\Controllers\InstagramCallbacksController;
use App\Http\Controllers\InstagramConnectController;
use App\Http\Controllers\LibraryBulkController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecycleBinController;
use App\Http\Controllers\MediaController;
use App\Http\Controllers\PicturesController;
use App\Http\Controllers\PublishingController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\TelegramConnectController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\TargetsController;
use Illuminate\Support\Facades\Route;

Route::view('privacy', 'privacy')->name('privacy');

// Called by Meta, not by a person: no login, the signed request is checked in the controller.
Route::post('instagram/deauthorize', [InstagramCallbacksController::class, 'deauthorize']);
Route::post('instagram/data-deletion', [InstagramCallbacksController::class, 'deletion']);
Route::get('instagram/data-deletion/{code}', [InstagramCallbacksController::class, 'status'])->name('instagram.deletion.status');

Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'show'])->name('register');
    Route::post('register', [RegisterController::class, 'store']);
    Route::get('login', [LoginController::class, 'show'])->name('login');
    Route::post('login', [LoginController::class, 'store']);
    Route::get('forgot-password', [PasswordResetController::class, 'request'])->name('password.request');
    Route::post('forgot-password', [PasswordResetController::class, 'email'])->middleware('throttle:5,1')->name('password.email');
    Route::get('reset-password/{token}', [PasswordResetController::class, 'form'])->name('password.reset');
    Route::post('reset-password', [PasswordResetController::class, 'update'])->name('password.update');
});

Route::post('logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Signed in but outside the workspace: a locked workspace cannot be entered, so these must not use the tenant middleware.
Route::middleware('auth')->group(function () {
    Route::get('deletion', [DeletionController::class, 'pending'])->name('deletion.pending');
    Route::post('deletion/restore', [DeletionController::class, 'restore'])->name('deletion.restore');
});

// `tenant` must come after `auth`: it needs the signed-in user to find the schema.
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('/', [DashboardController::class, 'home'])->name('home');
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('onboarding', [DashboardController::class, 'onboarding'])->name('onboarding');
    Route::post('onboarding/skip-targets', [DashboardController::class, 'skipTargets'])->name('onboarding.skip');
    Route::get('library', LibraryController::class)->name('library');
    Route::post('library/combine', [LibraryBulkController::class, 'combine'])->name('library.combine');
    Route::post('library/collection', [CollectionItemsController::class, 'addFromLibrary'])->name('library.collection');
    Route::get('collections', [CollectionsController::class, 'index'])->name('collections');
    Route::post('collections', [CollectionsController::class, 'store'])->name('collections.store');
    Route::get('collections/{collection}', [CollectionsController::class, 'show'])->name('collections.show');
    Route::patch('collections/{collection}', [CollectionsController::class, 'update'])->name('collections.update');
    Route::delete('collections/{collection}', [CollectionsController::class, 'destroy'])->name('collections.destroy');
    Route::patch('collections/{collection}/embed', [EmbedSettingsController::class, 'update'])->name('collections.embed');
    Route::post('collections/{collection}/embed/token', [EmbedSettingsController::class, 'regenerate'])->name('collections.token');
    Route::post('collections/{collection}/items/{item}/move', [CollectionItemsController::class, 'move'])->name('collections.move');
    Route::delete('collections/{collection}/items/{item}', [CollectionItemsController::class, 'remove'])->name('collections.remove');
    Route::post('library/delete', [RecycleBinController::class, 'bulkDelete'])->name('library.delete');
    Route::delete('content/{content}', [RecycleBinController::class, 'destroyContent'])->name('content.destroy');
    Route::get('recycle-bin', [RecycleBinController::class, 'index'])->name('recycle');
    Route::post('recycle-bin/{type}/{id}/restore', [RecycleBinController::class, 'restore'])->name('recycle.restore');
    Route::delete('recycle-bin/{type}/{id}', [RecycleBinController::class, 'forget'])->name('recycle.forget');
    Route::post('settings/delete-history', [DeletionController::class, 'request'])->name('deletion.request');
    Route::post('library/tag', [LibraryBulkController::class, 'tag'])->name('library.tag');
    Route::get('content/{content}', [ContentController::class, 'show'])->name('content.show');
    Route::patch('content/{content}', [ContentController::class, 'update'])->name('content.update');
    Route::patch('content/{content}/target/{target}', [ContentTargetController::class, 'update'])->name('content.target');
    Route::post('content/{content}/keep', [ContentSourceController::class, 'keep'])->name('content.keep');
    Route::post('content/{content}/take', [ContentSourceController::class, 'take'])->name('content.take');
    Route::post('content/{content}/parts', [ContentPartsController::class, 'add'])->name('content.parts.add');
    Route::post('content/{content}/parts/rebuild', [ContentPartsController::class, 'rebuild'])->name('content.parts.rebuild');
    Route::post('content/{content}/parts/{part}/move', [ContentPartsController::class, 'move'])->name('content.parts.move');
    Route::post('content/{content}/draft/{account}', [PublishingController::class, 'draft'])->name('content.draft');
    Route::delete('content/{content}/copies/{copy}', [PublishingController::class, 'discard'])->name('content.copy.remove');
    Route::post('content/{content}/copies/{copy}/publish', [PublishingController::class, 'publish'])->name('content.publish');
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::patch('settings/notifications', [SettingsController::class, 'notifications'])->name('settings.notifications');
    Route::patch('settings/pictures', [PicturesController::class, 'update'])->name('settings.pictures');
    Route::delete('settings/pictures', [PicturesController::class, 'destroy'])->name('settings.pictures.delete');
    Route::get('media/{copy}', [MediaController::class, 'show'])->name('media');
    Route::patch('settings/profile', [ProfileController::class, 'update'])->name('settings.profile');
    Route::put('settings/password', [ProfileController::class, 'password'])->name('settings.password');
    Route::post('settings/ai-keys', [AiKeyController::class, 'store'])->name('ai.store');
    Route::post('settings/ai-keys/{aiKey}/toggle', [AiKeyController::class, 'toggle'])->name('ai.toggle');
    Route::post('settings/ai-keys/{aiKey}/default', [AiKeyController::class, 'makeDefault'])->name('ai.default');
    Route::post('settings/ai-keys/{aiKey}/test', [AiKeyController::class, 'test'])->name('ai.test');
    Route::delete('settings/ai-keys/{aiKey}', [AiKeyController::class, 'destroy'])->name('ai.destroy');
    Route::post('accounts/telegram', [TelegramConnectController::class, 'store'])->name('accounts.telegram');
    Route::delete('content/{content}/parts/{part}', [ContentPartsController::class, 'remove'])->name('content.parts.remove');

    Route::get('accounts', [AccountsController::class, 'index'])->name('accounts');
    Route::get('accounts/instagram/connect', [InstagramConnectController::class, 'redirect'])->name('accounts.instagram');
    Route::get('accounts/instagram/callback', [InstagramConnectController::class, 'callback']);
    Route::post('accounts/token', [AccountTokenController::class, 'store'])->name('accounts.token');
    Route::post('accounts/{account}/refresh', [AccountTokenController::class, 'refresh'])->name('accounts.refresh');
    Route::patch('accounts/{account}', [AccountsController::class, 'update'])->name('accounts.update');
    Route::post('accounts/{account}/source', [AccountsController::class, 'makeSource'])->name('accounts.source');
    Route::post('accounts/{account}/sync', [AccountsController::class, 'syncNow'])->name('accounts.sync');
    Route::delete('accounts/{account}', [AccountsController::class, 'destroy'])->name('accounts.destroy');

    Route::post('targets', [TargetsController::class, 'store'])->name('targets.store');
    Route::patch('targets/{target}', [TargetsController::class, 'update'])->name('targets.update');
    Route::delete('targets/{target}', [TargetsController::class, 'destroy'])->name('targets.destroy');
});
