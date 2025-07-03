<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\TmdbController;


// Testes e informações da conta
Route::get('/test-account-details', [TmdbController::class, 'testAccountDetails'])
    ->name('api.tmdb.test-account-details');

// Gêneros
Route::get('/genres', [TmdbController::class, 'getMovieGenres'])
    ->name('api.tmdb.genres');

// Listas de filmes com paginação
Route::get('/movies/popular', [TmdbController::class, 'getPopularMovies'])
    ->name('api.tmdb.movies.popular');

// Filmes em alta
Route::get('/movies/trending', [TmdbController::class, 'getTrendingMovies'])
    ->name('api.tmdb.movies.trending');

// Filmes em cartaz
Route::get('/movies/discover', [TmdbController::class, 'discoverMovies'])
    ->name('api.tmdb.movies.discover');

// Busca e filtros
Route::get('/movies/search', [TmdbController::class, 'searchMovies'])
    ->name('api.tmdb.movies.search');

// Busca filmes por gênero específico
Route::get('/movies/genre/{genreId}', [TmdbController::class, 'getMoviesByGenre'])
    ->name('api.tmdb.movies.by-genre')
    ->where('genreId', '[0-9]+');

// Detalhes e múltiplos filmes
Route::get('/movies/{movieId}', [TmdbController::class, 'getMovieDetails'])
    ->name('api.tmdb.movies.details')
    ->where('movieId', '[0-9]+');

// Busca múltiplos filmes por IDs
Route::post('/movies/batch', [TmdbController::class, 'getMoviesByIds'])
    ->name('api.tmdb.movies.batch');

// Imagens de filmes
Route::get('/movies/{movieId}/images', [TmdbController::class, 'getMovieImages'])
    ->name('api.tmdb.movies.images')
    ->where('movieId', '[0-9]+');
