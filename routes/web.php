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

Route::get('/tmdb/test-account-details', [App\Http\Controllers\TmdbController::class, 'testAccountDetails'])
    ->middleware(['auth', 'verified'])
    ->name('tmdb.test-account-details');

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
