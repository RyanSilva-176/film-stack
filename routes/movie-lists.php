<?php

use App\Http\Controllers\MovieListController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'web'])->group(function () {

    // CRUD básico de listas
    Route::get('/movie-lists', [MovieListController::class, 'index'])
        ->name('movie-lists.index');

    Route::post('/movie-lists', [MovieListController::class, 'store'])
        ->name('movie-lists.store');

    Route::get('/movie-lists/{movieList}', [MovieListController::class, 'show'])
        ->name('movie-lists.show');

    Route::delete('/movie-lists/{movieList}', [MovieListController::class, 'destroy'])
        ->name('movie-lists.destroy');

    // Gerenciamento de filmes nas listas
    Route::post('/movie-lists/{movieList}/movies', [MovieListController::class, 'addMovie'])
        ->name('movie-lists.add-movie');

    Route::delete('/movie-lists/{movieList}/movies/{tmdbMovieId}', [MovieListController::class, 'removeMovie'])
        ->name('movie-lists.remove-movie');

    // Ações específicas
    Route::post('/movies/mark-watched', [MovieListController::class, 'markAsWatched'])
        ->name('movies.mark-watched');

    Route::post('/movies/toggle-like', [MovieListController::class, 'toggleLike'])
        ->name('movies.toggle-like');

    // Operações em lote
    Route::delete('/movie-lists/{movieList}/bulk-remove', [MovieListController::class, 'bulkRemoveMovies'])
        ->name('movie-lists.bulk-remove');

    Route::post('/movie-lists/bulk-move', [MovieListController::class, 'bulkMoveMovies'])
        ->name('movie-lists.bulk-move');

    Route::post('/movies/bulk-mark-watched', [MovieListController::class, 'bulkMarkWatched'])
        ->name('movies.bulk-mark-watched');
});
