<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('setlocale', LocaleController::class)->name('setlocale');

Route::domain('www.' . env('BASE_DOMAIN'))->group(function () {
    Route::get('/', fn() => response(200))->name('home');

    // Default locale routes (no prefix — matches prefix_except_default strategy)
    Route::get('/categories/{cateogry}', fn() => '200')->name('categories.show');
    Route::get('/articles/{article}', fn() => '200')->name('articles.show');
    Route::get('/services/{service}', fn() => '200')->name('services.show');

    // Other locales with prefix
    Route::prefix('{locale}')->group(function () {
        Route::get('/categories/{cateogry}', fn() => '200')->name('categories.show.localized');
        Route::get('/articles/{article}', fn() => '200')->name('articles.show.localized');
        Route::get('/services/{service}', fn() => '200')->name('services.show.localized');
    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/dashboard.php';
