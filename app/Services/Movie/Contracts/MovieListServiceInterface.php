<?php

namespace App\Services\Movie\Contracts;

use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\User;
use Illuminate\Support\Collection;

interface MovieListServiceInterface
{
    /**
     ** Cria as listas padrão para um usuário
     * @param User $user
     */
    public function createDefaultListsForUser(User $user): void;

    /**
     ** Busca todas as listas de um usuário
     * @param User $user
     * @return Collection<MovieList>
     */
    public function getUserLists(User $user): Collection;

    /**
     ** Cria uma lista personalizada
     * @param User $user
     * @param string $name
     * @param string|null $description
     * @param bool $isPublic
     * @return MovieList
     */
    public function createCustomList(User $user, string $name, ?string $description = null, bool $isPublic = false): MovieList;

    /**
     ** Adiciona filme a uma lista
     * @param MovieList $list
     * @param int $tmdbMovieId
     * @param array|null $metadata
     */
    public function addMovieToList(MovieList $list, int $tmdbMovieId, ?array $metadata = null): MovieListItem;

    /**
     ** Remove filme de uma lista
     * @param MovieList $list
     * @param int $tmdbMovieId
     * @return bool
     */
    public function removeMovieFromList(MovieList $list, int $tmdbMovieId): bool;

    /**
     ** Verifica se filme está em uma lista
     * @param MovieList $list
     * @param int $tmdbMovieId
     * @return bool
     */
    public function isMovieInList(MovieList $list, int $tmdbMovieId): bool;

    /**
     ** Busca filmes de uma lista com dados do TMDB
     * @param MovieList $list
     * @param int $page
     * @return array<MovieListItem>
     */
    public function getListMoviesWithDetails(MovieList $list, int $page = 1): array;

    /**
     ** Move filme assistido da watchlist para watched
     * @param User $user
     * @param int $tmdbMovieId
     * @param int|null $rating
     * @param string|null $notes
     */
    public function markMovieAsWatched(User $user, int $tmdbMovieId, ?int $rating = null, ?string $notes = null): void;

    /**
     ** Curte/descurte um filme
     * @param User $user
     * @param int $tmdbMovieId
     * @return bool
     */
    public function toggleMovieLike(User $user, int $tmdbMovieId): bool;
}
