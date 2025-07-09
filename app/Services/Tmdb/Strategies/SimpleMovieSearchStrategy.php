<?php

namespace App\Services\Tmdb\Strategies;

use App\Services\Tmdb\Services\TmdbBaseService;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\DTOs\SearchResultDTO;

class SimpleMovieSearchStrategy extends TmdbBaseService implements SearchStrategyInterface
{
    public function __construct(
        private MovieCollectionProcessor $processor
    ) {
        parent::__construct();
    }

    public function search(string $query, int $page, array $filters): ?SearchResultDTO
    {
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

        $results = $this->processor->processResults($results);

        if (isset($filters['sort_by']) && isset($results['results'])) {
            $results['results'] = $this->processor->sortResults($results['results'], $filters['sort_by']);
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }
}
