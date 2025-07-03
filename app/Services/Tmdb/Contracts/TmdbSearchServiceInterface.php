<?php

namespace App\Services\Tmdb\Contracts;

use App\Services\Tmdb\DTOs\SearchResultDTO;

interface TmdbSearchServiceInterface
{
    /**
     * Busca por filmes com paginação aprimorada e filtros
     */
    public function searchMovies(string $query, int $page = 1, array $filters = []): ?SearchResultDTO;

    /**
     * Busca multi-mídia (filmes, séries, pessoas)
     */
    public function searchMulti(string $query, int $page = 1, array $filters = []): ?SearchResultDTO;

    /**
     * Busca pessoas (atores, diretores)
     */
    public function searchPeople(string $query, int $page = 1, array $filters = []): ?SearchResultDTO;

    /**
     * Busca com sugestões inteligentes
     */
    public function searchWithSuggestions(string $query, int $page = 1): array;
}
