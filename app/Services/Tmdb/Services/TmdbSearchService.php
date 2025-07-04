<?php

namespace App\Services\Tmdb\Services;

use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\DTOs\SearchResultDTO;

class TmdbSearchService extends TmdbBaseService implements TmdbSearchServiceInterface
{
    protected TmdbGenreServiceInterface $genreService;

    public function __construct(TmdbGenreServiceInterface $genreService)
    {
        parent::__construct();
        $this->genreService = $genreService;
    }

    // TODO: Terminar de adicionar as tags de documentação

    /**
     * Busca por filmes com paginação aprimorada e filtros
     */
    public function searchMovies(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        $params = array_merge([
            'query' => trim($query),
            'page' => $page,
            'include_adult' => false,
        ], $filters);

        $params = $this->validatePaginationParams($params);

        $results = $this->makeRequest('/search/movie', $params);

        if (!$results) {
            return null;
        }

        // Enriquecer resultados com informações de gêneros
        if (isset($results['results']) && is_array($results['results'])) {
            $results['results'] = array_map(
                [$this->genreService, 'enrichMovieWithGenres'],
                $results['results']
            );
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     * Busca multi-mídia (filmes, séries, pessoas)
     */
    public function searchMulti(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        $params = array_merge([
            'query' => trim($query),
            'page' => $page,
            'include_adult' => false,
        ], $filters);

        $params = $this->validatePaginationParams($params);

        $results = $this->makeRequest('/search/multi', $params);

        if (!$results) {
            return null;
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     * Busca pessoas (atores, diretores)
     */
    public function searchPeople(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        $params = array_merge([
            'query' => trim($query),
            'page' => $page,
            'include_adult' => false,
        ], $filters);

        $params = $this->validatePaginationParams($params);

        $results = $this->makeRequest('/search/person', $params);

        if (!$results) {
            return null;
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     * Busca com sugestões inteligentes
     */
    public function searchWithSuggestions(string $query, int $page = 1): array
    {
        $searchResults = $this->searchMovies($query, $page);
        
        if (!$searchResults || !$searchResults->hasResults()) {
            // Se não encontrou resultados, tenta busca mais flexível
            $suggestions = $this->generateSearchSuggestions($query);
            
            return [
                'results' => $searchResults,
                'suggestions' => $suggestions,
                'has_suggestions' => !empty($suggestions)
            ];
        }

        return [
            'results' => $searchResults,
            'suggestions' => [],
            'has_suggestions' => false
        ];
    }

    /**
     * Gera sugestões de busca baseadas no termo original
     */
    protected function generateSearchSuggestions(string $query): array
    {
        $suggestions = [];
        
        // Remove palavras comuns que podem atrapalhar a busca
        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words = explode(' ', strtolower(trim($query)));
        $filteredWords = array_diff($words, $commonWords);

        if (count($filteredWords) > 1) {
            // Tenta buscar com palavras individuais
            foreach ($filteredWords as $word) {
                if (strlen($word) > 2) {
                    $wordResults = $this->searchMovies($word, 1);
                    if ($wordResults && $wordResults->hasResults()) {
                        $suggestions[] = $word;
                        break; // Adiciona apenas uma sugestão por palavra
                    }
                }
            }
        }

        // Tenta busca por gênero se o termo se parece com um gênero
        $genreSuggestions = $this->genreService->searchGenresByName($query);
        if (!empty($genreSuggestions)) {
            foreach ($genreSuggestions as $genre) {
                $suggestions[] = $genre->name;
            }
        }

        return array_unique($suggestions);
    }

    /**
     * Busca avançada com múltiplos filtros
     */
    public function advancedSearch(array $criteria): ?SearchResultDTO
    {
        $endpoint = '/discover/movie';
        $params = $this->buildAdvancedSearchParams($criteria);
        
        $results = $this->makeRequest($endpoint, $params);
        
        if (!$results) {
            return null;
        }

        // Enriquecer resultados com informações de gêneros
        if (isset($results['results']) && is_array($results['results'])) {
            $results['results'] = array_map(
                [$this->genreService, 'enrichMovieWithGenres'],
                $results['results']
            );
        }

        return SearchResultDTO::fromArray($results, '', $criteria);
    }

    /**
     * Constrói parâmetros para busca avançada
     */
    protected function buildAdvancedSearchParams(array $criteria): array
    {
        $params = [];

        // Filtros básicos
        if (isset($criteria['genre_id'])) {
            $params['with_genres'] = $criteria['genre_id'];
        }

        if (isset($criteria['year'])) {
            $params['year'] = $criteria['year'];
        }

        if (isset($criteria['rating_min'])) {
            $params['vote_average.gte'] = $criteria['rating_min'];
        }

        if (isset($criteria['rating_max'])) {
            $params['vote_average.lte'] = $criteria['rating_max'];
        }

        if (isset($criteria['release_date_min'])) {
            $params['release_date.gte'] = $criteria['release_date_min'];
        }

        if (isset($criteria['release_date_max'])) {
            $params['release_date.lte'] = $criteria['release_date_max'];
        }

        // Ordenação
        $params['sort_by'] = $criteria['sort_by'] ?? 'popularity.desc';

        // Paginação
        $params['page'] = $criteria['page'] ?? 1;

        return $this->validatePaginationParams($params);
    }

    /**
     * Busca por popularidade com filtros
     */
    public function searchPopular(array $filters = [], int $page = 1): ?SearchResultDTO
    {
        $criteria = array_merge([
            'sort_by' => 'popularity.desc',
            'page' => $page
        ], $filters);

        return $this->advancedSearch($criteria);
    }

    /**
     * Busca por melhor avaliação com filtros
     */
    public function searchTopRated(array $filters = [], int $page = 1): ?SearchResultDTO
    {
        $criteria = array_merge([
            'sort_by' => 'vote_average.desc',
            'vote_count.gte' => 100, // Apenas filmes com pelo menos 100 votos
            'page' => $page
        ], $filters);

        return $this->advancedSearch($criteria);
    }
}