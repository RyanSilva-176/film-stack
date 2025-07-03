<?php

namespace App\Services\Tmdb\DTOs;

use JsonSerializable;

class SearchResultDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $page,
        public readonly int $totalPages,
        public readonly int $totalResults,
        public readonly array $results,
        public readonly string $query = '',
        public readonly array $filters = []
    ) {}

    public static function fromArray(array $data, string $query = '', array $filters = []): self
    {
        $results = [];
        
        if (isset($data['results']) && is_array($data['results'])) {
            foreach ($data['results'] as $result) {
                if (isset($result['title'])) {
                    $results[] = MovieDTO::fromArray($result);
                } elseif (isset($result['name']) && isset($result['known_for_department'])) {
                    // Todo: Implementar PersonDTO casi seja necessário
                    $results[] = $result;
                } else {
                    // Todo: Outros tipos ou fallback
                    // ? Por enquanto mantém como array, pode criar DTOs específicos depois
                    $results[] = $result;
                }
            }
        }

        return new self(
            page: (int) ($data['page'] ?? 1),
            totalPages: min((int) ($data['total_pages'] ?? 1), 1000),
            totalResults: (int) ($data['total_results'] ?? 0),
            results: $results,
            query: $query,
            filters: $filters
        );
    }

    public function toArray(): array
    {
        return [
            'page' => $this->page,
            'total_pages' => $this->totalPages,
            'total_results' => $this->totalResults,
            'results' => array_map(function ($result) {
                if ($result instanceof MovieDTO) {
                    return $result->toArray();
                }
                return $result;
            }, $this->results),
            'query' => $this->query,
            'filters' => $this->filters,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function hasResults(): bool
    {
        return !empty($this->results);
    }

    public function hasNextPage(): bool
    {
        return $this->page < $this->totalPages;
    }

    public function hasPreviousPage(): bool
    {
        return $this->page > 1;
    }

    public function getMovies(): array
    {
        return array_filter($this->results, fn($result) => $result instanceof MovieDTO);
    }

    public function getPaginationInfo(): array
    {
        return [
            'current_page' => $this->page,
            'total_pages' => $this->totalPages,
            'total_results' => $this->totalResults,
            'has_next_page' => $this->hasNextPage(),
            'has_previous_page' => $this->hasPreviousPage(),
            'per_page' => 20,
        ];
    }
}
