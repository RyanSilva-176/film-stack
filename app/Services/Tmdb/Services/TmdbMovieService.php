<?php

namespace App\Services\Tmdb\Services;

use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\DTOs\MovieDTO;
use App\Services\Tmdb\Enrichers\MovieDataEnricher;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\Builders\SearchCriteriaBuilder;
use Illuminate\Support\Facades\Log;
use Exception;

class TmdbMovieService extends TmdbBaseService implements TmdbMovieServiceInterface
{
    public function __construct(
        private MovieDataEnricher $enricher,
        private MovieCollectionProcessor $processor
    ) {
        parent::__construct();
    }

    //? ==================== MÉTODOS DE LISTAGEM ====================

    public function getPopularMovies(int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint('/movie/popular', $page);
    }

    public function getNowPlayingMovies(int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint('/movie/now_playing', $page);
    }

    public function getTopRatedMovies(int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint('/movie/top_rated', $page);
    }

    public function getUpcomingMovies(int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint('/movie/upcoming', $page);
    }

    public function getTrendingMovies(string $timeWindow = 'day', int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint("/trending/movie/{$timeWindow}", $page);
    }

    //* ==================== MÉTODOS DE DETALHES ====================

    public function getMovieDetails(int $movieId, array $appendTo = []): ?MovieDTO
    {
        $params = $this->buildAppendToParams($appendTo);
        $movie = $this->makeRequest("/movie/{$movieId}", $params);

        if (!$movie) {
            return null;
        }

        $enrichedMovie = $this->enricher->enrichMovie($movie);
        return MovieDTO::fromArray($enrichedMovie);
    }

    public function getMoviesByIds(array $movieIds, array $appendTo = []): array
    {
        $movies = [];

        foreach ($movieIds as $movieId) {
            try {
                $movie = $this->getMovieDetails($movieId, $appendTo);
                if ($movie) {
                    $movies[] = $movie;
                }
            } catch (Exception $e) {
                Log::warning('Failed to fetch movie', [
                    'movie_id' => $movieId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $movies;
    }

    //* ==================== MÉTODOS DE DESCOBERTA E FILTROS ====================

    public function discoverMovies(array $filters = [], int $page = 1): ?array
    {
        $params = $this->buildDiscoverParams($filters, $page);
        $results = $this->makeRequest('/discover/movie', $params);

        return $this->processor->processResults($results);
    }

    public function getMoviesByGenre(int $genreId, int $page = 1, array $additionalFilters = []): ?array
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withGenre($genreId)
            ->withPage($page)
            ->withSorting('popularity.desc')
            ->withCustomFilters($additionalFilters)
            ->build();

        return $this->discoverMovies($criteria, $page);
    }

    public function getMoviesByYear(int $year, int $page = 1, string $sortBy = 'popularity.desc'): ?array
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withYear($year)
            ->withPage($page)
            ->withSorting($sortBy)
            ->build();

        return $this->discoverMovies($criteria, $page);
    }

    public function getMoviesByDecade(int $decade, int $page = 1): ?array
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withDateRange("{$decade}-01-01", ($decade + 9) . "-12-31")
            ->withPage($page)
            ->withSorting('popularity.desc')
            ->build();

        return $this->discoverMovies($criteria, $page);
    }

    public function getMoviesByMinRating(float $minRating, int $page = 1, int $minVotes = 100): ?array
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withRating($minRating, $minVotes)
            ->withPage($page)
            ->withSorting('vote_average.desc')
            ->build();

        return $this->discoverMovies($criteria, $page);
    }

    //* ==================== MÉTODOS DE RELACIONAMENTO ====================

    public function getSimilarMovies(int $movieId, int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint("/movie/{$movieId}/similar", $page);
    }

    public function getMovieRecommendations(int $movieId, int $page = 1): ?array
    {
        return $this->getMoviesByEndpoint("/movie/{$movieId}/recommendations", $page);
    }

    //* ==================== MÉTODOS DE DADOS ESPECÍFICOS ====================

    public function getMovieCredits(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/credits");
    }

    public function getMovieVideos(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/videos");
    }

    public function getMovieImages(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/images");
    }

    public function getMovieReviews(int $movieId, int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest("/movie/{$movieId}/reviews", $params);
    }

    public function getMovieKeywords(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/keywords");
    }

    public function getMovieReleaseDates(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/release_dates");
    }

    //* ==================== MÉTODOS DE BUSCA ESPECIALIZADA ====================

    public function findMovieByExternalId(string $externalId, string $source = 'imdb_id'): ?MovieDTO
    {
        $results = $this->makeRequest("/find/{$externalId}", [
            'external_source' => $source
        ]);

        if (!$results || empty($results['movie_results'])) {
            return null;
        }

        $movie = $this->enricher->enrichMovie($results['movie_results'][0]);
        return MovieDTO::fromArray($movie);
    }

    //* ==================== MÉTODOS DE CONVERSÃO E UTILITÁRIOS ====================

    public function convertToMovieDTOs(array $movies): array
    {
        return array_map(
            fn($movie) => is_array($movie) ? MovieDTO::fromArray($movie) : $movie,
            $movies
        );
    }

    public function getMoviesForListing(string $listType = 'popular', int $page = 1): ?array
    {
        return match ($listType) {
            'now_playing' => $this->getNowPlayingMovies($page),
            'top_rated' => $this->getTopRatedMovies($page),
            'upcoming' => $this->getUpcomingMovies($page),
            'trending' => $this->getTrendingMovies('day', $page),
            default => $this->getPopularMovies($page)
        };
    }

    //? ==================== MÉTODOS PRIVADOS AUXILIARES ====================

    /**
     ** Método genérico para buscar filmes por endpoint com paginação
     */
    private function getMoviesByEndpoint(string $endpoint, int $page): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest($endpoint, $params);
        return $this->processor->processResults($results);
    }

    /**
     ** Constrói parâmetros para append_to_response
     */
    private function buildAppendToParams(array $appendTo): array
    {
        $defaultAppendTo = ['credits', 'videos', 'images', 'recommendations', 'similar'];
        $appendTo = array_unique(array_merge($defaultAppendTo, $appendTo));

        return empty($appendTo) ? [] : ['append_to_response' => implode(',', $appendTo)];
    }

    /**
     ** Constrói parâmetros para discover endpoint
     */
    private function buildDiscoverParams(array $filters, int $page): array
    {
        $params = array_merge($filters, ['page' => $page]);
        return $this->validatePaginationParams($params);
    }
}
