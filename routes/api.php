<?php

use Illuminate\Support\Facades\Route;

// Rotas TMDB API - Protegidas com autenticação
Route::prefix('tmdb')
    ->middleware(['web', 'auth', 'verified'])
    ->group(base_path('routes/api/tmdb/routes.php'));

// ROtas Dev sem auth
Route::prefix('dev/tmdb')
    ->group(base_path('routes/api/tmdb/routes.php'));

// Rotas de Listas de Filmes - Protegidas com autenticação
Route::prefix('api')
    ->middleware(['web', 'auth', 'verified'])
    ->group(base_path('routes/movie-lists.php'));
