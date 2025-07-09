<?php

namespace App\Services\Tmdb\Services;

use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\DTOs\SearchResultDTO;
use App\Services\Tmdb\Strategies\SearchStrategyInterface;
use App\Services\Tmdb\Strategies\SimpleMovieSearchStrategy;
use App\Services\Tmdb\Strategies\GenreFilteredSearchStrategy;
use App\Services\Tmdb\Strategies\AdvancedSearchStrategy;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;

class TmdbSearchServiceRefactored extends TmdbBaseService implements TmdbSearchServiceInterface
{
    public function __construct(
        private SimpleMovieSearchStrategy $simpleSearch,
        private GenreFilteredSearchStrategy $genreSearch,
        private AdvancedSearchStrategy $advancedSearch,
        private MovieCollectionProcessor $processor
    ) {
        parent::__construct();
    }

    //? ==================== MÉTODOS PÚBLICOS DE BUSCA ====================

    public function searchMovies(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        $strategy = $this->selectSearchStrategy($filters);
        return $strategy->search($query, $page, $filters);
    }

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

        //* Processa resultados multi-mídia
        if (isset($results['results'])) {
            $results['results'] = $this->processMultiResults($results['results']);
        }

        return SearchResultDTO::fromArray($results, $query, $filters);
    }

    public function searchPeople(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        $params = array_merge([
            'query' => trim($query),
            'page' => $page,
            'include_adult' => false,
        ], $filters);

        $params = $this->validatePaginationParams($params);
        $results = $this->makeRequest('/search/person', $params);

        return $results ? SearchResultDTO::fromArray($results, $query, $filters) : null;
    }

    public function searchWithSuggestions(string $query, int $page = 1): array
    {
        $mainSearch = $this->searchMovies($query, $page);

        if (!$mainSearch || empty($mainSearch->results)) {
            return [
                'results' => [],
                'suggestions' => $this->generateSearchSuggestions($query),
                'corrected_query' => $this->suggestQueryCorrection($query)
            ];
        }

        return [
            'results' => $mainSearch->results,
            'pagination' => $mainSearch->toArray()['pagination'] ?? [],
            'suggestions' => [],
            'original_query' => $query
        ];
    }

    public function advancedSearch(array $criteria): ?SearchResultDTO
    {
        $query = $criteria['query'] ?? '';
        $page = $criteria['page'] ?? 1;

        return $this->advancedSearch->search($query, $page, $criteria);
    }

    //? ==================== MÉTODOS PRIVADOS ====================

    /**
     * Seleciona a estratégia de busca apropriada baseada nos filtros
     */
    private function selectSearchStrategy(array $filters): SearchStrategyInterface
    {
        //* Se tem filtro de gênero, usa busca híbrida
        if (isset($filters['with_genres'])) {
            return $this->genreSearch;
        }

        //* Se tem filtros avançados, usa discover
        if ($this->hasAdvancedFilters($filters)) {
            return $this->advancedSearch;
        }

        //* Caso contrário, usa busca simples
        return $this->simpleSearch;
    }

    /**
     ** Verifica se há filtros que requerem busca avançada
     */
    private function hasAdvancedFilters(array $filters): bool
    {
        $advancedFilters = [
            'year',
            'vote_average.gte',
            'vote_count.gte',
            'release_date.gte',
            'release_date.lte',
            'with_runtime.gte',
            'with_runtime.lte',
            'with_original_language'
        ];

        return !empty(array_intersect(array_keys($filters), $advancedFilters));
    }

    /**
     ** Processa resultados de busca multi-mídia
     * TODO: Melhorar lógica de processamento futuramente para lidar com diferentes tipos de mídia
     */
    private function processMultiResults(array $results): array
    {
        return array_map(function ($item) {
            if (isset($item['media_type']) && $item['media_type'] === 'movie') {
                $processedResults = $this->processor->processResults(['results' => [$item]]);
                return $processedResults['results'][0] ?? $item;
            }

            return $item;
        }, $results);
    }

    /**
     ** Gera sugestões de busca alternativas
     */
    private function generateSearchSuggestions(string $query): array
    {
        $suggestions = [];
        $queryWords = explode(' ', trim($query));

        $commonTerms = [
            'action' => 'ação',
            'comedy' => 'comédia',
            'drama' => 'drama',
            'horror' => 'terror',
            'sci-fi' => 'ficção científica',
            'romance' => 'romance'
        ];

        foreach ($queryWords as $word) {
            $lowerWord = strtolower($word);
            if (isset($commonTerms[$lowerWord])) {
                $suggestions[] = str_replace($word, $commonTerms[$lowerWord], $query);
            }
        }

        return array_slice(array_unique($suggestions), 0, 5);
    }

    /**
     ** Sugere correções para a query de busca
     */
    private function suggestQueryCorrection(string $query): ?string
    {
        $corrections = [
            'acao' => 'ação',
            'comedia' => 'comédia',
            'ficcao' => 'ficção',
            'animacao' => 'animação'
        ];

        $correctedQuery = $query;
        foreach ($corrections as $wrong => $correct) {
            $correctedQuery = str_ireplace($wrong, $correct, $correctedQuery);
        }

        return $correctedQuery !== $query ? $correctedQuery : null;
    }

    //? ==================== MÉTODOS LEGADOS PARA COMPATIBILIDADE ====================

    /**
     *! @deprecated Use searchMovies() com filtros específicos
     */
    protected function hybridSearchWithGenre(string $query, int $page, array $filters): ?SearchResultDTO
    {
        return $this->genreSearch->search($query, $page, $filters);
    }

    /**
     *! @deprecated Use AdvancedSearchStrategy diretamente
     */
    protected function advancedSearchWithQuery(string $query, array $criteria): ?SearchResultDTO
    {
        $page = $criteria['page'] ?? 1;
        return $this->advancedSearch->search($query, $page, $criteria);
    }

    /**
     *! @deprecated Lógica movida para MovieCollectionProcessor
     */
    protected function sortResults(array $results, string $sortBy): array
    {
        return $this->processor->sortResults($results, $sortBy);
    }
}
