<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\SocialiteController;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Welcome');
})->name('home');

Route::get('dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');


Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'create'])->where('provider', 'google');

Route::get('/auth/{provider}/callback', [SocialiteController::class, 'store'])->where('provider', 'google');

Route::get('/test-connectivity', [SocialiteController::class, 'testConnectivity'])
    ->name('test.connectivity');

Route::prefix('tmdb')->middleware(['auth', 'verified'])->group(function () {
    Route::get('/test-account-details', [App\Http\Controllers\TmdbController::class, 'testAccountDetails'])
        ->name('tmdb.test-account-details');

    Route::get('/movies/popular', [App\Http\Controllers\TmdbController::class, 'getPopularMovies'])
        ->name('tmdb.movies.popular');

    Route::get('/movies/search', [App\Http\Controllers\TmdbController::class, 'searchMovies'])
        ->name('tmdb.movies.search');

    Route::get('/movies/{movieId}', [App\Http\Controllers\TmdbController::class, 'getMovieDetails'])
        ->name('tmdb.movies.details');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
