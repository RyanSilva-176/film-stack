<?php

namespace App\Services\Tmdb\Processors;

use App\Services\Tmdb\Enrichers\MovieDataEnricher;


class MovieCollectionProcessor
{
    public function __construct(
        private MovieDataEnricher $enricher
    ) {}

    /**
     ** Processa resultados da API TMDB enriquecendo os dados
     */
    public function processResults(?array $results): ?array
    {
        if (!$results || !isset($results['results'])) {
            return $results;
        }

        $results['results'] = $this->enricher->enrichMovies($results['results']);
        return $results;
    }

    /**
     ** Ordena uma coleção de filmes baseado no critério especificado
     */
    public function sortResults(array $movies, string $sortBy): array
    {
        return match ($sortBy) {
            'popularity.desc' => $this->sortByPopularity($movies, 'desc'),
            'popularity.asc' => $this->sortByPopularity($movies, 'asc'),
            'vote_average.desc' => $this->sortByRating($movies, 'desc'),
            'vote_average.asc' => $this->sortByRating($movies, 'asc'),
            'release_date.desc' => $this->sortByReleaseDate($movies, 'desc'),
            'release_date.asc' => $this->sortByReleaseDate($movies, 'asc'),
            'title.asc' => $this->sortByTitle($movies, 'asc'),
            'title.desc' => $this->sortByTitle($movies, 'desc'),
            default => $movies
        };
    }

    /**
     ** Filtra filmes por texto livre em múltiplos campos
     */
    public function filterByText(array $movies, string $searchText): array
    {
        if (empty(trim($searchText))) {
            return $movies;
        }

        $queryLower = strtolower(trim($searchText));
        $queryWords = array_filter(explode(' ', $queryLower), fn($word) => strlen($word) > 2);

        return array_values(array_filter($movies, function ($movie) use ($queryLower, $queryWords) {
            $title = strtolower($movie['title'] ?? '');
            $originalTitle = strtolower($movie['original_title'] ?? '');
            $overview = strtolower($movie['overview'] ?? '');

            //? Busca exata
            if (
                str_contains($title, $queryLower) ||
                str_contains($originalTitle, $queryLower) ||
                str_contains($overview, $queryLower)
            ) {
                return true;
            }

            //? Busca por palavras individuais
            foreach ($queryWords as $word) {
                if (
                    str_contains($title, $word) ||
                    str_contains($originalTitle, $word) ||
                    str_contains($overview, $word)
                ) {
                    return true;
                }
            }

            return false;
        }));
    }

    /**
     ** Aplica paginação a uma coleção de filmes
     */
    public function paginateResults(array $movies, int $page, int $perPage = 20): array
    {
        $totalResults = count($movies);
        $totalPages = max(1, ceil($totalResults / $perPage));
        $offset = ($page - 1) * $perPage;
        $paginatedMovies = array_slice($movies, $offset, $perPage);

        return [
            'results' => $paginatedMovies,
            'total_results' => $totalResults,
            'total_pages' => $totalPages,
            'page' => $page,
        ];
    }

    /**
     ** Ordena por popularidade
     */
    private function sortByPopularity(array $movies, string $direction): array
    {
        usort(
            $movies,
            fn($a, $b) =>
            $direction === 'desc'
                ? $b['popularity'] <=> $a['popularity']
                : $a['popularity'] <=> $b['popularity']
        );
        return $movies;
    }

    /**
     ** Ordena por avaliação
     */
    private function sortByRating(array $movies, string $direction): array
    {
        usort(
            $movies,
            fn($a, $b) =>
            $direction === 'desc'
                ? $b['vote_average'] <=> $a['vote_average']
                : $a['vote_average'] <=> $b['vote_average']
        );
        return $movies;
    }

    /**
     ** Ordena por data de lançamento
     */
    private function sortByReleaseDate(array $movies, string $direction): array
    {
        usort(
            $movies,
            fn($a, $b) =>
            $direction === 'desc'
                ? ($b['release_date'] ?? '') <=> ($a['release_date'] ?? '')
                : ($a['release_date'] ?? '') <=> ($b['release_date'] ?? '')
        );
        return $movies;
    }

    /**
     ** Ordena por título
     */
    private function sortByTitle(array $movies, string $direction): array
    {
        usort(
            $movies,
            fn($a, $b) =>
            $direction === 'desc'
                ? strcasecmp($b['title'] ?? '', $a['title'] ?? '')
                : strcasecmp($a['title'] ?? '', $b['title'] ?? '')
        );
        return $movies;
    }
}
