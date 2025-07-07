<?php

namespace App\Services\Tmdb\Services;

use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;
use App\Services\Tmdb\DTOs\SearchResultDTO;

class TmdbSearchService extends TmdbBaseService implements TmdbSearchServiceInterface
{
    protected TmdbGenreServiceInterface $genreService;
    protected TmdbImageServiceInterface $imageService;

    public function __construct(TmdbGenreServiceInterface $genreService, TmdbImageServiceInterface $imageService)
    {
        parent::__construct();
        $this->genreService = $genreService;
        $this->imageService = $imageService;
    }


    /**
     ** Gera URL do poster do filme
     * @param string|null $posterPath
     * @param string $size
     * @return string|null
     */
    protected function getPosterUrl(?string $posterPath, string $size = 'w500'): ?string
    {
        return $this->imageService->getPosterUrl($posterPath, $size);
    }

    /**
     ** Gera URL do backdrop do filme
     * @param string|null $backdropPath
     * @param string $size
     * @return string|null
     */
    protected function getBackdropUrl(?string $backdropPath, string $size = 'w1280'): ?string
    {
        return $this->imageService->getBackdropUrl($backdropPath, $size);
    }

    /**
     ** Busca por filmes com paginação aprimorada e filtros
     * @param string $query
     * @param int $page
     * @param array $filters
     * @return SearchResultDTO|null
     */
    public function searchMovies(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        if (isset($filters['with_genres'])) {
            return $this->hybridSearchWithGenre($query, $page, $filters);
        }

        $hasAdvancedFilters = isset($filters['year']);

        if ($hasAdvancedFilters) {
            $criteria = [
                'page' => $page,
                ...$filters
            ];

            return $this->advancedSearchWithQuery($query, $criteria);
        }

        $params = array_merge([
            'query' => trim($query),
            'page' => $page,
            'include_adult' => false,
        ], array_diff_key($filters, ['sort_by' => '']));

        $params = $this->validatePaginationParams($params);

        $results = $this->makeRequest('/search/movie', $params);

        if (!$results) {
            return null;
        }

        if (isset($results['results']) && is_array($results['results'])) {
            $results['results'] = array_map(function ($movie) {
                $movie = $this->genreService->enrichMovieWithGenres($movie);

                $movie['poster_url'] = $this->getPosterUrl($movie['poster_path'] ?? null);
                $movie['backdrop_url'] = $this->getBackdropUrl($movie['backdrop_path'] ?? null);

                $movie['overview'] = $movie['overview'] ?? '';
                $movie['release_date'] = $movie['release_date'] ?? null;
                $movie['vote_average'] = (float) ($movie['vote_average'] ?? 0);
                $movie['vote_count'] = (int) ($movie['vote_count'] ?? 0);
                $movie['adult'] = (bool) ($movie['adult'] ?? false);
                $movie['video'] = (bool) ($movie['video'] ?? false);
                $movie['popularity'] = (float) ($movie['popularity'] ?? 0);

                return $movie;
            }, $results['results']);

            if (isset($filters['sort_by'])) {
                $results['results'] = $this->sortResults($results['results'], $filters['sort_by']);
            }
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     ** Busca híbrida combinando texto e gênero
     * @param string $query
     * @param int $page
     * @param array $filters
     * @return SearchResultDTO|null
     */
    protected function hybridSearchWithGenre(string $query, int $page, array $filters): ?SearchResultDTO
    {
        $targetGenreId = (int) $filters['with_genres'];
        
        // Primeiro fazer busca por texto para encontrar filmes relevantes
        $params = [
            'query' => trim($query),
            'page' => 1, // Começar da página 1 para buscar mais resultados
            'include_adult' => $filters['include_adult'] ?? false,
        ];

        $allResults = [];
        $totalSearched = 0;
        $maxPages = 5; // Limitar a busca a 5 páginas para evitar timeout
        
        // Buscar em múltiplas páginas até encontrar filmes suficientes do gênero desejado
        for ($searchPage = 1; $searchPage <= $maxPages; $searchPage++) {
            $params['page'] = $searchPage;
            $searchResults = $this->makeRequest('/search/movie', $params);
            
            if (!$searchResults || !isset($searchResults['results'])) {
                break;
            }
            
            foreach ($searchResults['results'] as $movie) {
                $movie = $this->genreService->enrichMovieWithGenres($movie);
                
                // Verificar se o filme tem o gênero desejado
                $movieGenreIds = $movie['genre_ids'] ?? [];
                if (in_array($targetGenreId, $movieGenreIds)) {
                    $movie['poster_url'] = $this->getPosterUrl($movie['poster_path'] ?? null);
                    $movie['backdrop_url'] = $this->getBackdropUrl($movie['backdrop_path'] ?? null);
                    
                    $movie['overview'] = $movie['overview'] ?? '';
                    $movie['release_date'] = $movie['release_date'] ?? null;
                    $movie['vote_average'] = (float) ($movie['vote_average'] ?? 0);
                    $movie['vote_count'] = (int) ($movie['vote_count'] ?? 0);
                    $movie['adult'] = (bool) ($movie['adult'] ?? false);
                    $movie['video'] = (bool) ($movie['video'] ?? false);
                    $movie['popularity'] = (float) ($movie['popularity'] ?? 0);
                    
                    $allResults[] = $movie;
                }
            }
            
            $totalSearched += count($searchResults['results']);
            
            // Se não há mais páginas ou já encontramos resultados suficientes
            if ($searchPage >= ($searchResults['total_pages'] ?? 1) || count($allResults) >= 100) {
                break;
            }
        }
        
        // Aplicar ordenação se especificada
        if (isset($filters['sort_by'])) {
            $allResults = $this->sortResults($allResults, $filters['sort_by']);
        }
        
        // Aplicar paginação aos resultados filtrados
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $paginatedResults = array_slice($allResults, $offset, $perPage);
        
        // Construir resposta paginada
        $totalResults = count($allResults);
        $totalPages = max(1, ceil($totalResults / $perPage));
        
        $results = [
            'results' => $paginatedResults,
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => $page,
        ];
        
        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     ** Busca avançada com query de texto
     * @param string $query
     * @param array $criteria
     * @return SearchResultDTO|null
     */
    protected function advancedSearchWithQuery(string $query, array $criteria): ?SearchResultDTO
    {
        $endpoint = '/discover/movie';
        $params = $this->buildAdvancedSearchParams($criteria);

        $results = $this->makeRequest($endpoint, $params);

        if (!$results) {
            return null;
        }

        if (isset($results['results']) && is_array($results['results'])) {
            $results['results'] = array_map(function ($movie) {
                $movie = $this->genreService->enrichMovieWithGenres($movie);

                $movie['poster_url'] = $this->getPosterUrl($movie['poster_path'] ?? null);
                $movie['backdrop_url'] = $this->getBackdropUrl($movie['backdrop_path'] ?? null);

                // Garante que campos essenciais estejam presentes
                $movie['overview'] = $movie['overview'] ?? '';
                $movie['release_date'] = $movie['release_date'] ?? null;
                $movie['vote_average'] = (float) ($movie['vote_average'] ?? 0);
                $movie['vote_count'] = (int) ($movie['vote_count'] ?? 0);
                $movie['adult'] = (bool) ($movie['adult'] ?? false);
                $movie['video'] = (bool) ($movie['video'] ?? false);
                $movie['popularity'] = (float) ($movie['popularity'] ?? 0);

                return $movie;
            }, $results['results']);

            if (!empty(trim($query))) {
                $queryLower = strtolower(trim($query));
                $queryWords = explode(' ', $queryLower);

                $results['results'] = array_filter($results['results'], function ($movie) use ($queryLower, $queryWords) {
                    $title = strtolower($movie['title'] ?? '');
                    $originalTitle = strtolower($movie['original_title'] ?? '');
                    $overview = strtolower($movie['overview'] ?? '');

                    if (
                        strpos($title, $queryLower) !== false ||
                        strpos($originalTitle, $queryLower) !== false ||
                        strpos($overview, $queryLower) !== false
                    ) {
                        return true;
                    }

                    foreach ($queryWords as $word) {
                        if (strlen($word) > 2 && (
                            strpos($title, $word) !== false ||
                            strpos($originalTitle, $word) !== false ||
                            strpos($overview, $word) !== false
                        )) {
                            return true;
                        }
                    }

                    return false;
                });

                $results['results'] = array_values($results['results']);

                $results['total_results'] = count($results['results']);
                $results['total_pages'] = max(1, ceil($results['total_results'] / 20));
            }
        }

        return SearchResultDTO::fromArray($results, $query, $criteria);
    }

    /**
     ** Busca multi-mídia
     * @param string $query
     * @param int $page
     * @param array $filters
     * @return SearchResultDTO|null
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

        if (isset($results['results']) && is_array($results['results'])) {
            $results['results'] = array_map(function ($item) {
                if (isset($item['media_type']) && $item['media_type'] === 'movie') {
                    $item = $this->genreService->enrichMovieWithGenres($item);
                    $item['poster_url'] = $this->getPosterUrl($item['poster_path'] ?? null);
                    $item['backdrop_url'] = $this->getBackdropUrl($item['backdrop_path'] ?? null);

                    // Garante que campos essenciais estejam presentes para filmes
                    $item['overview'] = $item['overview'] ?? '';
                    $item['release_date'] = $item['release_date'] ?? null;
                    $item['vote_average'] = (float) ($item['vote_average'] ?? 0);
                    $item['vote_count'] = (int) ($item['vote_count'] ?? 0);
                    $item['adult'] = (bool) ($item['adult'] ?? false);
                    $item['video'] = (bool) ($item['video'] ?? false);
                    $item['popularity'] = (float) ($item['popularity'] ?? 0);
                }
                return $item;
            }, $results['results']);
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     ** Busca pessoas
     * @param string $query
     * @param int $page
     * @param array $filters
     * @return SearchResultDTO|null
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
     ** Busca com sugestões inteligentes
     * @param string $query
     * @param int $page
     * @return array
     */
    public function searchWithSuggestions(string $query, int $page = 1): array
    {
        $searchResults = $this->searchMovies($query, $page);

        if (!$searchResults || !$searchResults->hasResults()) {
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
     ** Gera sugestões de busca baseadas no termo original
     * @param string $query
     * @return array
     */
    protected function generateSearchSuggestions(string $query): array
    {
        $suggestions = [];

        $commonWords = ['the', 'a', 'an', 'and', 'or', 'but', 'in', 'on', 'at', 'to', 'for', 'of', 'with', 'by'];
        $words = explode(' ', strtolower(trim($query)));
        $filteredWords = array_diff($words, $commonWords);

        if (count($filteredWords) > 1) {
            foreach ($filteredWords as $word) {
                if (strlen($word) > 2) {
                    $wordResults = $this->searchMovies($word, 1);
                    if ($wordResults && $wordResults->hasResults()) {
                        $suggestions[] = $word;
                        break;
                    }
                }
            }
        }

        $genreSuggestions = $this->genreService->searchGenresByName($query);
        if (!empty($genreSuggestions)) {
            foreach ($genreSuggestions as $genre) {
                $suggestions[] = $genre->name;
            }
        }

        return array_unique($suggestions);
    }

    /**
     ** Busca avançada com múltiplos filtros
     * @param array $criteria
     * @return SearchResultDTO|null
     */
    public function advancedSearch(array $criteria): ?SearchResultDTO
    {
        $endpoint = '/discover/movie';
        $params = $this->buildAdvancedSearchParams($criteria);

        $results = $this->makeRequest($endpoint, $params);

        if (!$results) {
            return null;
        }

        if (isset($results['results']) && is_array($results['results'])) {
            $results['results'] = array_map(function ($movie) {
                $movie = $this->genreService->enrichMovieWithGenres($movie);

                $movie['poster_url'] = $this->getPosterUrl($movie['poster_path'] ?? null);
                $movie['backdrop_url'] = $this->getBackdropUrl($movie['backdrop_path'] ?? null);

                return $movie;
            }, $results['results']);
        }

        return SearchResultDTO::fromArray($results, '', $criteria);
    }

    /**
     ** Constrói parâmetros para busca avançada
     * @param array $criteria
     * @return array
     */
    protected function buildAdvancedSearchParams(array $criteria): array
    {
        $params = [];

        if (isset($criteria['genre_id'])) {
            $params['with_genres'] = $criteria['genre_id'];
        } elseif (isset($criteria['with_genres'])) {
            $params['with_genres'] = $criteria['with_genres'];
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

        $params['sort_by'] = $criteria['sort_by'] ?? 'popularity.desc';

        $params['page'] = $criteria['page'] ?? 1;

        return $this->validatePaginationParams($params);
    }

    /**
     ** Busca por popularidade com filtros
     * @param array $filters
     * @param int $page
     * @return SearchResultDTO|null
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
     ** Busca por melhor avaliação com filtros
     * @param array $filters
     * @param int $page
     * @return SearchResultDTO|null
     */
    public function searchTopRated(array $filters = [], int $page = 1): ?SearchResultDTO
    {
        $criteria = array_merge([
            'sort_by' => 'vote_average.desc',
            'vote_count.gte' => 100,
            'page' => $page
        ], $filters);

        return $this->advancedSearch($criteria);
    }

    /**
     ** Ordena resultados
     * @param array $movies
     * @param string $sortBy
     * @return array
     */
    protected function sortResults(array $movies, string $sortBy): array
    {
        switch ($sortBy) {
            case 'title_asc':
                usort($movies, fn($a, $b) => strcasecmp($a['title'] ?? '', $b['title'] ?? ''));
                break;
            case 'title_desc':
                usort($movies, fn($a, $b) => strcasecmp($b['title'] ?? '', $a['title'] ?? ''));
                break;
            case 'release_date_asc':
                usort($movies, fn($a, $b) => ($a['release_date'] ?? '0000-00-00') <=> ($b['release_date'] ?? '0000-00-00'));
                break;
            case 'release_date_desc':
                usort($movies, fn($a, $b) => ($b['release_date'] ?? '0000-00-00') <=> ($a['release_date'] ?? '0000-00-00'));
                break;
            case 'vote_average_asc':
                usort($movies, fn($a, $b) => ($a['vote_average'] ?? 0) <=> ($b['vote_average'] ?? 0));
                break;
            case 'vote_average_desc':
                usort($movies, fn($a, $b) => ($b['vote_average'] ?? 0) <=> ($a['vote_average'] ?? 0));
                break;
            case 'popularity_asc':
                usort($movies, fn($a, $b) => ($a['popularity'] ?? 0) <=> ($b['popularity'] ?? 0));
                break;
            case 'popularity_desc':
            default:
                usort($movies, fn($a, $b) => ($b['popularity'] ?? 0) <=> ($a['popularity'] ?? 0));
                break;
        }

        return $movies;
    }
}
