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
    // Testes e informações da conta
    Route::get('/test-account-details', [App\Http\Controllers\TmdbController::class, 'testAccountDetails'])
        ->name('tmdb.test-account-details');

    // Gêneros
    Route::get('/genres', [App\Http\Controllers\TmdbController::class, 'getMovieGenres'])
        ->name('tmdb.genres');

    // Listas de filmes com paginação
    Route::get('/movies/popular', [App\Http\Controllers\TmdbController::class, 'getPopularMovies'])
        ->name('tmdb.movies.popular');
    
    Route::get('/movies/trending', [App\Http\Controllers\TmdbController::class, 'getTrendingMovies'])
        ->name('tmdb.movies.trending');
    
    Route::get('/movies/discover', [App\Http\Controllers\TmdbController::class, 'discoverMovies'])
        ->name('tmdb.movies.discover');

    // Busca e filtros
    Route::get('/movies/search', [App\Http\Controllers\TmdbController::class, 'searchMovies'])
        ->name('tmdb.movies.search');
    
    Route::get('/movies/genre/{genreId}', [App\Http\Controllers\TmdbController::class, 'getMoviesByGenre'])
        ->name('tmdb.movies.by-genre')
        ->where('genreId', '[0-9]+');

    // Detalhes e múltiplos filmes
    Route::get('/movies/{movieId}', [App\Http\Controllers\TmdbController::class, 'getMovieDetails'])
        ->name('tmdb.movies.details')
        ->where('movieId', '[0-9]+');
    
    Route::post('/movies/batch', [App\Http\Controllers\TmdbController::class, 'getMoviesByIds'])
        ->name('tmdb.movies.batch');
    
    Route::get('/movies/{movieId}/images', [App\Http\Controllers\TmdbController::class, 'getMovieImages'])
        ->name('tmdb.movies.images')
        ->where('movieId', '[0-9]+');
});

require __DIR__ . '/settings.php';
require __DIR__ . '/auth.php';
