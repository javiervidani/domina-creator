<?php

use App\Http\Controllers\AccountsController;
use App\Http\Controllers\AccountTokenController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\ContentController;
use App\Http\Controllers\InstagramCallbacksController;
use App\Http\Controllers\InstagramConnectController;
use App\Http\Controllers\LibraryBulkController;
use App\Http\Controllers\LibraryController;
use App\Http\Controllers\TargetsController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/library');
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

// `tenant` must come after `auth`: it needs the signed-in user to find the schema.
Route::middleware(['auth', 'tenant'])->group(function () {
    Route::get('library', LibraryController::class)->name('library');
    Route::post('library/combine', [LibraryBulkController::class, 'combine'])->name('library.combine');
    Route::post('library/tag', [LibraryBulkController::class, 'tag'])->name('library.tag');
    Route::get('content/{content}', [ContentController::class, 'show'])->name('content.show');
    Route::patch('content/{content}', [ContentController::class, 'update'])->name('content.update');

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
