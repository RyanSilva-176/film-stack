<?php

namespace App\Services\Tmdb\Strategies;

use App\Services\Tmdb\Services\TmdbBaseService;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\DTOs\SearchResultDTO;


class GenreFilteredSearchStrategy extends TmdbBaseService implements SearchStrategyInterface
{
    private const MAX_SEARCH_PAGES = 5;
    private const RESULTS_PER_PAGE = 20;

    public function __construct(
        private MovieCollectionProcessor $processor
    ) {
        parent::__construct();
    }

    public function search(string $query, int $page, array $filters): ?SearchResultDTO
    {
        $targetGenreId = (int) $filters['with_genres'];
        $allResults = $this->searchAndFilterByGenre($query, $targetGenreId, $filters);

        if (isset($filters['sort_by'])) {
            $allResults = $this->processor->sortResults($allResults, $filters['sort_by']);
        }

        $paginatedResults = $this->processor->paginateResults($allResults, $page, self::RESULTS_PER_PAGE);

        return SearchResultDTO::fromArray($paginatedResults, $query, $filters);
    }

    /**
     ** Busca por texto e filtra resultados por gênero
     */
    private function searchAndFilterByGenre(string $query, int $targetGenreId, array $filters): array
    {
        $allResults = [];
        $searchParams = [
            'query' => trim($query),
            'include_adult' => $filters['include_adult'] ?? false,
        ];

        //? Busca em múltiplas páginas até encontrar resultados suficientes
        for ($searchPage = 1; $searchPage <= self::MAX_SEARCH_PAGES; $searchPage++) {
            $searchParams['page'] = $searchPage;
            $searchResults = $this->makeRequest('/search/movie', $searchParams);

            if (!$searchResults || !isset($searchResults['results'])) {
                break;
            }

            $filteredMovies = $this->filterMoviesByGenre($searchResults['results'], $targetGenreId);
            $allResults = array_merge($allResults, $filteredMovies);

            if ($searchPage >= ($searchResults['total_pages'] ?? 1) || count($allResults) >= 100) {
                break;
            }
        }

        return $allResults;
    }

    /**
     ** Filtra filmes por gênero específico
     */
    private function filterMoviesByGenre(array $movies, int $targetGenreId): array
    {
        $filteredMovies = [];

        foreach ($movies as $movie) {
            $movieGenreIds = $movie['genre_ids'] ?? [];

            if (in_array($targetGenreId, $movieGenreIds)) {
                $enrichedMovie = $this->processor->processResults(['results' => [$movie]]);

                if (isset($enrichedMovie['results'][0])) {
                    $filteredMovies[] = $enrichedMovie['results'][0];
                }
            }
        }

        return $filteredMovies;
    }
}
