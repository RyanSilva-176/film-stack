<?php

namespace App\Services\Tmdb\Services;

use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;
use App\Services\Tmdb\DTOs\MovieDTO;
use Illuminate\Support\Facades\Log;
use Exception;

class TmdbMovieService extends TmdbBaseService implements TmdbMovieServiceInterface
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
     ** Busca filmes populares com paginação
     * @param int $page
     * @return array|null
     */
    public function getPopularMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest('/movie/popular', $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Busca filmes em cartaz com paginação
     * @param int $page
     * @return array|null
     */
    public function getNowPlayingMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest('/movie/now_playing', $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Busca filmes mais bem avaliados com paginação
     * @param int $page
     * @return array|null
     */
    public function getTopRatedMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest('/movie/top_rated', $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     * Busca próximos lançamentos com paginação
     * @param int $page
     * @return array|null
     */
    public function getUpcomingMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest('/movie/upcoming', $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Busca detalhes de um filme específico
     * @param int $movieId
     * @param array $appendTo
     */
    public function getMovieDetails(int $movieId, array $appendTo = []): ?MovieDTO
    {
        $params = [];

        $defaultAppendTo = ['credits', 'videos', 'images', 'recommendations', 'similar'];
        $appendTo = array_unique(array_merge($defaultAppendTo, $appendTo));

        if (!empty($appendTo)) {
            $params['append_to_response'] = implode(',', $appendTo);
        }

        $movie = $this->makeRequest("/movie/{$movieId}", $params);

        if (!$movie) {
            return null;
        }

        $movie = $this->genreService->enrichMovieWithGenres($movie);

        return MovieDTO::fromArray($movie);
    }

    /**
     ** Busca múltiplos filmes por IDs
     * @param array $movieIds
     * @param array $appendTo
     * @return array
     */
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
                Log::warning('Falha ao buscar filme', [
                    'movie_id' => $movieId,
                    'error' => $e->getMessage()
                ]);
            }
        }

        return $movies;
    }

    /**
     ** Busca filmes trending
     * @param string $timeWindow
     * @param int $page
     * @return array|null
     */
    public function getTrendingMovies(string $timeWindow = 'day', int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest("/trending/movie/{$timeWindow}", $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Descobre filmes com filtros avançados
     * @param array $filters
     * @param int $page
     * @return array|null
     */
    public function discoverMovies(array $filters = [], int $page = 1): ?array
    {
        $params = array_merge($filters, ['page' => $page]);
        $params = $this->validatePaginationParams($params);

        $results = $this->makeRequest('/discover/movie', $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Busca filmes por gênero específico
     * @param int $genreId
     * @param int $page
     * @param array $additionalFilters
     * @return array|null
     */
    public function getMoviesByGenre(int $genreId, int $page = 1, array $additionalFilters = []): ?array
    {
        $filters = array_merge([
            'with_genres' => $genreId,
            'sort_by' => 'popularity.desc'
        ], $additionalFilters);

        return $this->discoverMovies($filters, $page);
    }

    /**
     ** Busca filmes similares
     * @param int $movieId
     * @param int $page
     * @return array|null
     */
    public function getSimilarMovies(int $movieId, int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest("/movie/{$movieId}/similar", $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Busca recomendações de filmes
     * @param int $movieId
     * @param int $page
     * @return array|null
     */
    public function getMovieRecommendations(int $movieId, int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        $results = $this->makeRequest("/movie/{$movieId}/recommendations", $params);

        return $this->enrichResultsWithGenres($results);
    }

    /**
     ** Busca créditos do filme
     * @param int $movieId
     * @return array|null
     */
    public function getMovieCredits(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/credits");
    }

    /**
     ** Busca vídeos do filme
     * @param int $movieId
     * @return array|null
     */
    public function getMovieVideos(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/videos");
    }

    /**
     ** Busca imagens do filme
     * @param int $movieId
     * @return array|null
     */
    public function getMovieImages(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/images");
    }

    /**
     ** Busca reviews do filme
     * @param int $movieId
     * @param int $page
     * @return array|null
     */
    public function getMovieReviews(int $movieId, int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest("/movie/{$movieId}/reviews", $params);
    }

    /**
     ** Busca palavras-chave do filme
     * @param int $movieId
     * @return array|null
     */
    public function getMovieKeywords(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/keywords");
    }

    /**
     ** Busca informações de lançamento do filme
     * @param int $movieId
     * @return array|null
     */
    public function getMovieReleaseDates(int $movieId): ?array
    {
        return $this->makeRequest("/movie/{$movieId}/release_dates");
    }

    /**
     ** Busca filme por ID externo
     * @param string $externalId
     * @param string $source
     * @return MovieDTO|null
     */
    public function findMovieByExternalId(string $externalId, string $source = 'imdb_id'): ?MovieDTO
    {
        $results = $this->makeRequest("/find/{$externalId}", [
            'external_source' => $source
        ]);

        if (!$results || empty($results['movie_results'])) {
            return null;
        }

        $movie = $results['movie_results'][0];
        $movie = $this->genreService->enrichMovieWithGenres($movie);

        return MovieDTO::fromArray($movie);
    }

    /**
     ** Busca filmes por ano específico
     * @param int $year
     * @param int $page
     * @param string $sortBy
     * @return array|null
     */
    public function getMoviesByYear(int $year, int $page = 1, string $sortBy = 'popularity.desc'): ?array
    {
        return $this->discoverMovies([
            'year' => $year,
            'sort_by' => $sortBy
        ], $page);
    }

    /**
     ** Busca filmes por década
     * @param int $decade
     * @param int $page
     * @return array|null
     */
    public function getMoviesByDecade(int $decade, int $page = 1): ?array
    {
        $startYear = $decade;
        $endYear = $decade + 9;

        return $this->discoverMovies([
            'release_date.gte' => "{$startYear}-01-01",
            'release_date.lte' => "{$endYear}-12-31",
            'sort_by' => 'popularity.desc'
        ], $page);
    }

    /**
     ** Busca filmes por avaliação mínima
     * @param float $minRating
     * @param int $page
     * @param int $minVotes
     * @return array|null
     */
    public function getMoviesByMinRating(float $minRating, int $page = 1, int $minVotes = 100): ?array
    {
        return $this->discoverMovies([
            'vote_average.gte' => $minRating,
            'vote_count.gte' => $minVotes,
            'sort_by' => 'vote_average.desc'
        ], $page);
    }

    /**
     ** Traz ps gêneros e URLs de imagens
     * @param array|null $results
     * @return array|null
     */
    protected function enrichResultsWithGenres(?array $results): ?array
    {
        if (!$results || !isset($results['results'])) {
            return $results;
        }

        $results['results'] = array_map(function ($movie) {
            $movie = $this->genreService->enrichMovieWithGenres($movie);

            $movie['poster_url'] = $this->imageService->getPosterUrl($movie['poster_path'] ?? null);
            $movie['backdrop_url'] = $this->imageService->getBackdropUrl($movie['backdrop_path'] ?? null);

            return $movie;
        }, $results['results']);

        return $results;
    }

    /**
     ** Converte array de filmes para DTOs
     * @param array $movies
     * @return array
     */
    public function convertToMovieDTOs(array $movies): array
    {
        return array_map(
            fn($movie) => is_array($movie) ? MovieDTO::fromArray($movie) : $movie,
            $movies
        );
    }

    /**
     ** Busca filmes com informações completas para listagem
     * @param string $listType
     * @param int $page
     * @return array|null
     */
    public function getMoviesForListing(string $listType = 'popular', int $page = 1): ?array
    {
        switch ($listType) {
            case 'now_playing':
                return $this->getNowPlayingMovies($page);
            case 'top_rated':
                return $this->getTopRatedMovies($page);
            case 'upcoming':
                return $this->getUpcomingMovies($page);
            case 'trending':
                return $this->getTrendingMovies('day', $page);
            default:
                return $this->getPopularMovies($page);
        }
    }
}
