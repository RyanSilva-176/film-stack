<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovieListController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::middleware(['auth', 'verified', 'web'])->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');
    

    Route::get('lists/liked', [MovieListController::class, 'likedPage'])->name('lists.liked');
    Route::get('lists/watchlist', [MovieListController::class, 'watchlistPage'])->name('lists.watchlist');
    Route::get('lists/watched', [MovieListController::class, 'watchedPage'])->name('lists.watched');
    Route::get('lists/custom', [MovieListController::class, 'customPage'])->name('lists.custom');
    Route::get('lists/{movieList}', [MovieListController::class, 'customListDetail'])->name('lists.detail');
});

Route::get('ui', function () {
    return Inertia::render('ButtonDemo');
})->middleware(['web'])->name('ui');


Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'create'])->where('provider', 'google');

Route::get('/auth/{provider}/callback', [SocialiteController::class, 'store'])->where('provider', 'google');

Route::get('/test-connectivity', [SocialiteController::class, 'testConnectivity'])
    ->name('test.connectivity');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
