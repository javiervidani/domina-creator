<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\LibraryController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/library');

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
});
