<?php

namespace App\Services\Movie\Contracts;

use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\User;

interface MovieListServiceInterface
{
    /**
     ** Cria as listas padrão para um usuário
     */
    public function createDefaultListsForUser(User $user): void;

    /**
     * Busca todas as listas de um usuário
     */
    public function getUserLists(User $user): \Illuminate\Database\Eloquent\Collection;

    /**
     * Cria uma lista personalizada
     */
    public function createCustomList(User $user, string $name, ?string $description = null, bool $isPublic = false): MovieList;

    /**
     * Adiciona filme a uma lista
     */
    public function addMovieToList(MovieList $list, int $tmdbMovieId, ?array $metadata = null): MovieListItem;

    /**
     * Remove filme de uma lista
     */
    public function removeMovieFromList(MovieList $list, int $tmdbMovieId): bool;

    /**
     * Verifica se filme está em uma lista
     */
    public function isMovieInList(MovieList $list, int $tmdbMovieId): bool;

    /**
     * Busca filmes de uma lista com dados do TMDB
     */
    public function getListMoviesWithDetails(MovieList $list, int $page = 1): array;

    /**
     * Move filme assistido da watchlist para watched
     */
    public function markMovieAsWatched(User $user, int $tmdbMovieId, ?int $rating = null, ?string $notes = null): void;

    /**
     * Curte/descurte um filme
     */
    public function toggleMovieLike(User $user, int $tmdbMovieId): bool;
}
