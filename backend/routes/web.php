<?php

use App\Http\Controllers\LocaleController;
use Illuminate\Support\Facades\Route;

Route::get('setlocale', LocaleController::class)->name('setlocale');

Route::domain('www.' . env('BASE_DOMAIN'))->group(function () {
    Route::get('/', fn() => response(200))->name('home');

    Route::prefix('{locale}')->group(function () {

        // Category route
        Route::get('/categories/{cateogry}', fn() => '200')->name('categories.show');

        // Subcategory route
        Route::get('/categories/sub/{cateogry}', fn() => '200')->name('subcategories.show');

        // Service route
        Route::get('/services/{service}', fn() => '200')->name('services.show');
    });
});

require __DIR__ . '/auth.php';
require __DIR__ . '/dashboard.php';
