<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TmdbController;

//* Rotas TMDB API - Protegidas com autenticação
Route::prefix('tmdb')
    ->middleware(['web', 'auth', 'verified'])
    ->group(base_path('routes/api/tmdb/routes.php'));

//* Rotas Dev sem auth
Route::prefix('dev/tmdb')
    ->group(base_path('routes/api/tmdb/routes.php'));

//* Rotas de Listas de Filmes - Protegidas com autenticação
Route::middleware(['web', 'auth', 'verified'])
    ->group(base_path('routes/movie-lists.php'));

//* Rotas TMDB Públicas
Route::prefix('public/tmdb')->group(function () {
    Route::get('/movies/trending', [TmdbController::class, 'getTrendingMovies'])
        ->name('api.public.tmdb.movies.trending');
    Route::get('/movies/popular', [TmdbController::class, 'getPopularMovies'])
        ->name('api.public.tmdb.movies.popular');
    Route::get('/movies/{movieId}', [TmdbController::class, 'getMovieDetails'])
        ->name('api.public.tmdb.movies.details')
        ->where('movieId', '[0-9]+');
    Route::get('/genres', [TmdbController::class, 'getMovieGenres'])
        ->name('api.public.tmdb.genres');
});
