<?php

namespace App\Services\Tmdb\Contracts;

use App\Services\Tmdb\DTOs\MovieDTO;

interface TmdbMovieServiceInterface
{
    /**
     * Busca filmes populares com paginação
     */
    public function getPopularMovies(int $page = 1): ?array;

    /**
     * Busca filmes em cartaz com paginação
     */
    public function getNowPlayingMovies(int $page = 1): ?array;

    /**
     * Busca filmes mais bem avaliados com paginação
     */
    public function getTopRatedMovies(int $page = 1): ?array;

    /**
     * Busca próximos lançamentos com paginação
     */
    public function getUpcomingMovies(int $page = 1): ?array;

    /**
     * Busca detalhes de um filme específico
     */
    public function getMovieDetails(int $movieId, array $appendTo = []): ?MovieDTO;

    /**
     * Busca múltiplos filmes por IDs
     */
    public function getMoviesByIds(array $movieIds, array $appendTo = []): array;

    /**
     * Busca filmes trending
     */
    public function getTrendingMovies(string $timeWindow = 'day', int $page = 1): ?array;

    /**
     * Descobre filmes com filtros avançados
     */
    public function discoverMovies(array $filters = [], int $page = 1): ?array;

    /**
     * Busca filmes por gênero específico
     */
    public function getMoviesByGenre(int $genreId, int $page = 1, array $additionalFilters = []): ?array;

    /**
     * Busca filmes com informações completas para listagem
     */
    public function getMoviesForListing(string $listType = 'popular', int $page = 1): ?array;
}
