<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\Tmdb\Facades\Tmdb;
use Illuminate\Http\JsonResponse;
use Exception;

class TmdbController extends Controller
{
    public function __construct()
    {
        if (!config('services.tmdb.api_key')) {
            abort(500, 'TMDB API Key não configurada no .env');
        }

        if (!config('services.tmdb.account_id')) {
            abort(500, 'TMDB Account ID não configurado no .env');
        }
    }

    /**
     * * Testa a obtenção dos detalhes da conta TMDB
     * @return JsonResponse
     */
    public function testAccountDetails(): JsonResponse
    {
        $accountId = config('services.tmdb.account_id');

        if (!$accountId) {
            return response()->json(['error' => 'TMDB Account ID não configurado no .env'], 500);
        }

        try {
            $details = Tmdb::getAccountDetails($accountId);

            if (!$details || isset($details['status_code'])) {
                $errorMsg = 'Falha ao buscar detalhes da conta TMDB';
                if (isset($details['status_message'])) {
                    $errorMsg .= ': ' . $details['status_message'];
                }
                return response()->json(['error' => $errorMsg], 500);
            }

            return response()->json([
                'success' => true,
                'account_details' => $details
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * * Busca lista de gêneros de filmes
     * @return JsonResponse
     */
    public function getMovieGenres(): JsonResponse
    {
        try {
            $genres = Tmdb::getMovieGenres();

            if (!$genres || !isset($genres['genres'])) {
                return response()->json(['error' => 'Falha ao buscar gêneros de filmes'], 500);
            }

            return response()->json([
                'success' => true,
                'genres' => $genres['genres']
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca filmes populares com paginação
     * @return JsonResponse
     */
    public function getPopularMovies(): JsonResponse
    {
        try {
            $page = request('page', 1);
            $movies = Tmdb::getPopularMovies($page);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao buscar filmes populares'], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $movies['results'] ?? [],
                'pagination' => Tmdb::getPaginationInfo($movies)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Realiza busca de filmes com paginação e filtros
     * @return JsonResponse
     */
    public function searchMovies(): JsonResponse
    {
        $query = request('query');
        $page = request('page', 1);
        $includeAdult = request('include_adult', false);
        $year = request('year');
        $sortBy = request('sortBy');
        $genreId = request('genreId');

        if (!$query || trim($query) === '') {
            return response()->json(['error' => 'Query é obrigatória e não pode estar vazia'], 400);
        }

        try {
            $filters = [
                'include_adult' => filter_var($includeAdult, FILTER_VALIDATE_BOOLEAN)
            ];

            if ($year) {
                $filters['year'] = (int) $year;
            }
            
            if ($sortBy) {
                $filters['sort_by'] = $sortBy;
            }

            if ($genreId) {
                $filters['with_genres'] = (int) $genreId;
            }

            $results = Tmdb::searchMovies($query, $page, $filters);

            if (!$results) {
                return response()->json(['error' => 'Falha na busca de filmes'], 500);
            }

            return response()->json([
                'success' => true,
                'query' => $query,
                'data' => $results->results ?? [],
                'pagination' => $results->getPaginationInfo()
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca detalhes de um filme específico
     * @param int $movieId
     * @return JsonResponse
     */
    public function getMovieDetails(int $movieId): JsonResponse
    {
        try {
            $appendTo = request('append_to_response', []);

            if (is_string($appendTo)) {
                $appendTo = explode(',', $appendTo);
            }

            $movieDTO = Tmdb::getMovieDetails($movieId, $appendTo);

            if (!$movieDTO) {
                return response()->json(['error' => 'Filme não encontrado'], 404);
            }

            $movie = $movieDTO->toArray();

            $movie['image_urls'] = [
                'poster' => [
                    'small' => Tmdb::getPosterUrl($movieDTO->posterPath, 'w185'),
                    'medium' => Tmdb::getPosterUrl($movieDTO->posterPath, 'w342'),
                    'large' => Tmdb::getPosterUrl($movieDTO->posterPath, 'w500'),
                    'xlarge' => Tmdb::getPosterUrl($movieDTO->posterPath, 'w780'),
                    'original' => Tmdb::getPosterUrl($movieDTO->posterPath, 'original'),
                ],
                'backdrop' => [
                    'small' => Tmdb::getBackdropUrl($movieDTO->backdropPath, 'w300'),
                    'medium' => Tmdb::getBackdropUrl($movieDTO->backdropPath, 'w780'),
                    'large' => Tmdb::getBackdropUrl($movieDTO->backdropPath, 'w1280'),
                    'original' => Tmdb::getBackdropUrl($movieDTO->backdropPath, 'original'),
                ]
            ];

            if (isset($movie['production_companies'])) {
                foreach ($movie['production_companies'] as &$company) {
                    if (isset($company['logo_path']) && $company['logo_path']) {
                        $company['logo_url'] = Tmdb::getLogoUrl($company['logo_path']);
                    }
                }
            }

            if (isset($movie['belongs_to_collection']['poster_path'])) {
                $movie['belongs_to_collection']['poster_url'] = Tmdb::getPosterUrl(
                    $movie['belongs_to_collection']['poster_path']
                );
                $movie['belongs_to_collection']['backdrop_url'] = Tmdb::getBackdropUrl(
                    $movie['belongs_to_collection']['backdrop_path'] ?? null
                );
            }

            return response()->json($movie);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca para buscar URLs de imagens de um filme
     * @param int $movieId
     * @return JsonResponse
     */
    public function getMovieImages(int $movieId): JsonResponse
    {
        try {
            $movieDTO = Tmdb::getMovieDetails($movieId);

            if (!$movieDTO) {
                return response()->json(['error' => 'Filme não encontrado'], 404);
            }

            $images = [
                'movie_id' => $movieId,
                'title' => $movieDTO->title ?? 'Título não disponível',
                'poster_urls' => Tmdb::getImageUrls($movieDTO->posterPath, 'poster'),
                'backdrop_urls' => Tmdb::getImageUrls($movieDTO->backdropPath, 'backdrop'),
            ];

            $movieArray = $movieDTO->toArray();
            if (isset($movieArray['belongs_to_collection'])) {
                $images['collection'] = [
                    'name' => $movieArray['belongs_to_collection']['name'],
                    'poster_urls' => Tmdb::getImageUrls(
                        $movieArray['belongs_to_collection']['poster_path'] ?? null,
                        'poster'
                    ),
                    'backdrop_urls' => Tmdb::getImageUrls(
                        $movieArray['belongs_to_collection']['backdrop_path'] ?? null,
                        'backdrop'
                    ),
                ];
            }

            return response()->json($images);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca filmes em alta
     * @param string $timeWindow 'day' ou 'week'
     * @return JsonResponse
     */
    public function getTrendingMovies(): JsonResponse
    {
        try {
            $timeWindow = request('time_window', 'day');
            $page = request('page', 1);

            $movies = Tmdb::getTrendingMovies($timeWindow, $page);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao buscar filmes em alta'], 500);
            }

            return response()->json([
                'success' => true,
                'time_window' => $timeWindow,
                'data' => $movies['results'] ?? [],
                'pagination' => Tmdb::getPaginationInfo($movies)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca filmes com filtros avançados
     * @return JsonResponse
     */
    public function discoverMovies(): JsonResponse
    {
        try {
            $page = request('page', 1);
            $filters = request()->only([
                'with_genres',
                'without_genres',
                'sort_by',
                'primary_release_year',
                'vote_average.gte',
                'vote_average.lte',
                'with_runtime.gte',
                'with_runtime.lte'
            ]);

            $movies = Tmdb::discoverMovies($filters, $page);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao descobrir filmes'], 500);
            }

            return response()->json([
                'success' => true,
                'filters' => $filters,
                'data' => $movies['results'] ?? [],
                'pagination' => Tmdb::getPaginationInfo($movies)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca filmes por gênero específico
     * @param int $genreId
     * @return JsonResponse
     */
    public function getMoviesByGenre(int $genreId): JsonResponse
    {
        try {
            $page = request('page', 1);
            $sortBy = request('sort_by', 'popularity.desc');
            $year = request('year');

            $additionalFilters = [
                'sort_by' => $sortBy
            ];

            if ($year) {
                $additionalFilters['year'] = (int) $year;
            }

            $movies = Tmdb::getMoviesByGenre($genreId, $page, $additionalFilters);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao buscar filmes do gênero'], 500);
            }

            $genre = Tmdb::getGenreById($genreId);

            return response()->json([
                'success' => true,
                'genre' => $genre,
                'sort_by' => $sortBy,
                'data' => $movies['results'] ?? [],
                'pagination' => Tmdb::getPaginationInfo($movies)
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca múltiplos filmes por IDs
     * @return JsonResponse
     */
    public function getMoviesByIds(): JsonResponse
    {
        $movieIds = request('movie_ids');

        if (!$movieIds || !is_array($movieIds)) {
            return response()->json(['error' => 'movie_ids deve ser um array de IDs'], 400);
        }

        $movieIds = array_filter($movieIds, 'is_numeric');
        $movieIds = array_map('intval', $movieIds);

        if (count($movieIds) > 20) {
            return response()->json(['error' => 'Máximo de 20 filmes por requisição'], 400);
        }

        try {
            $movies = Tmdb::getMoviesByIds($movieIds);

            return response()->json([
                'success' => true,
                'requested_ids' => $movieIds,
                'found_movies' => count($movies),
                'data' => $movies
            ]);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
