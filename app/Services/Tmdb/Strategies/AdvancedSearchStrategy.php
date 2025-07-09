<?php

namespace App\Services\Tmdb\Strategies;

use App\Services\Tmdb\Services\TmdbBaseService;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\DTOs\SearchResultDTO;

class AdvancedSearchStrategy extends TmdbBaseService implements SearchStrategyInterface
{
    public function __construct(
        private MovieCollectionProcessor $processor
    ) {
        parent::__construct();
    }

    public function search(string $query, int $page, array $filters): ?SearchResultDTO
    {
        $params = $this->buildDiscoverParams($filters, $page);
        $results = $this->makeRequest('/discover/movie', $params);

        if (!$results) {
            return null;
        }

        $results = $this->processor->processResults($results);

        if (!empty(trim($query)) && isset($results['results'])) {
            $results['results'] = $this->processor->filterByText($results['results'], $query);

            $paginatedResults = $this->processor->paginateResults($results['results'], $page);
            $results = array_merge($results, $paginatedResults);
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    /**
     ** Constrói parâmetros para o endpoint /discover/movie
     */
    private function buildDiscoverParams(array $filters, int $page): array
    {
        $params = ['page' => $page];

        $filterMap = [
            'year' => 'year',
            'with_genres' => 'with_genres',
            'vote_average.gte' => 'vote_average.gte',
            'vote_count.gte' => 'vote_count.gte',
            'release_date.gte' => 'release_date.gte',
            'release_date.lte' => 'release_date.lte',
            'with_runtime.gte' => 'with_runtime.gte',
            'with_runtime.lte' => 'with_runtime.lte',
            'with_original_language' => 'with_original_language',
            'sort_by' => 'sort_by',
            'include_adult' => 'include_adult',
        ];

        foreach ($filterMap as $filterKey => $paramKey) {
            if (isset($filters[$filterKey])) {
                $params[$paramKey] = $filters[$filterKey];
            }
        }

        if (!isset($params['sort_by'])) {
            $params['sort_by'] = 'popularity.desc';
        }

        return $this->validatePaginationParams($params);
    }
}
