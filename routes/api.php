<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\TranslationController;
use Illuminate\Support\Facades\Route;

// Authentication Routes
Route::controller(AuthController::class)->group(function () {
    Route::post('register', 'register')->name('register');
    Route::post('login', 'login')->name('login');
});

Route::middleware(['auth:sanctum'])->group(function () {

    // Authentication Route For Logout
    Route::controller(AuthController::class)->group(function () {
        Route::post('logout', 'logout')->name('logout');
    });

    // Translation Routes
    Route::controller(TranslationController::class)->group(function () {
        Route::get('translations', 'index');
        Route::post('translation-store', 'store');
        Route::get('translation-search', 'show');
        Route::put('translation-update/{id}', 'update');
        Route::get('translations-export', 'exportJson');
    });

});

