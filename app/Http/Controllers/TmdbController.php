<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;
use Illuminate\Http\JsonResponse;

class TmdbController extends Controller
{
    public function __construct(protected TmdbService $tmdbService)
    {
        // * Verifica se a chave da API TMDB está configurada
        if (!config('services.tmdb.api_key')) {
            abort(500, 'TMDB API Key não configurada no .env');
        }

        // * Verifica se o Account ID da TMDB está configurado
        if (!config('services.tmdb.account_id')) {
            abort(500, 'TMDB Account ID não configurado no .env');
        }

        //* Garante que o serviço TMDB está disponível
        if (!$this->tmdbService) {
            abort(500, 'Serviço TMDB não disponível');
        }
    }

    /**
     * * Testa a obtenção dos detalhes da conta TMDB
     * @return JsonResponse
     */
    public function testAccountDetails(): JsonResponse
    {
        $accountId = config('services.tmdb.account_id');

        //* Verifica se o Account ID está configurado
        if (!$accountId) {
            //! [ERROR] Verifica se o Account ID está configurado
            return response()->json(['error' => 'TMDB Account ID não configurado no .env'], 500);
        }

        try {
            $details = $this->tmdbService->getAccountDetails($accountId);

            //! [ERROR] Trata caso não retorne detalhes
            if (!$details || isset($details['status_code'])) {
                $errorMsg = 'Falha ao buscar detalhes da conta TMDB';
                if (isset($details['status_message'])) {
                    $errorMsg .= ': ' . $details['status_message'];
                }
                return response()->json(['error' => $errorMsg], 500);
            }

            //? Retorna detalhes da conta com sucesso
            return response()->json([
                'success' => true,
                'account_details' => $details
            ]);
        } catch (\Exception $e) {
            //! [ERROR] Captura e retorna erro interno
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca lista de gêneros de filmes
     */
    public function getMovieGenres(): JsonResponse
    {
        try {
            $genres = $this->tmdbService->getMovieGenres();

            if (!$genres || !isset($genres['genres'])) {
                return response()->json(['error' => 'Falha ao buscar gêneros de filmes'], 500);
            }

            return response()->json([
                'success' => true,
                'genres' => $genres['genres']
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Busca filmes populares com paginação aprimorada
     */
    public function getPopularMovies(): JsonResponse
    {
        try {
            $page = request('page', 1);
            $movies = $this->tmdbService->getPopularMovies($page);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao buscar filmes populares'], 500);
            }

            return response()->json([
                'success' => true,
                'data' => $movies['results'] ?? [],
                'pagination' => $this->tmdbService->getPaginationInfo($movies)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Realiza busca de filmes com paginação e filtros aprimorados
     */
    public function searchMovies(): JsonResponse
    {
        $query = request('query');
        $page = request('page', 1);
        $includeAdult = request('include_adult', false);

        if (!$query || trim($query) === '') {
            return response()->json(['error' => 'Query é obrigatória e não pode estar vazia'], 400);
        }

        try {
            $filters = [
                'include_adult' => filter_var($includeAdult, FILTER_VALIDATE_BOOLEAN)
            ];

            $results = $this->tmdbService->searchMovies($query, $page, $filters);

            if (!$results) {
                return response()->json(['error' => 'Falha na busca de filmes'], 500);
            }

            return response()->json([
                'success' => true,
                'query' => $query,
                'data' => $results['results'] ?? [],
                'pagination' => $this->tmdbService->getPaginationInfo($results)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * *Busca detalhes de um filme específico
     * @param int $movieId
     * @return JsonResponse
     */
    public function getMovieDetails(int $movieId): JsonResponse
    {
        try {
            $appendTo = request('append_to_response', []);

            //* Permite múltiplos parâmetros via string separada por vírgula
            if (is_string($appendTo)) {
                $appendTo = explode(',', $appendTo);
            }

            $movie = $this->tmdbService->getMovieDetails($movieId, $appendTo);

            //! [ERROR] Retorna erro se o filme não for encontrado
            if (!$movie) {
                return response()->json(['error' => 'Filme não encontrado'], 404);
            }

            // * Adiciona URLs das imagens ao resultado
            $movie['image_urls'] = [
                'poster' => [
                    'small' => $this->tmdbService->getPosterUrl($movie['poster_path'] ?? null, 'w185'),
                    'medium' => $this->tmdbService->getPosterUrl($movie['poster_path'] ?? null, 'w342'),
                    'large' => $this->tmdbService->getPosterUrl($movie['poster_path'] ?? null, 'w500'),
                    'xlarge' => $this->tmdbService->getPosterUrl($movie['poster_path'] ?? null, 'w780'),
                    'original' => $this->tmdbService->getPosterUrl($movie['poster_path'] ?? null, 'original'),
                ],
                'backdrop' => [
                    'small' => $this->tmdbService->getBackdropUrl($movie['backdrop_path'] ?? null, 'w300'),
                    'medium' => $this->tmdbService->getBackdropUrl($movie['backdrop_path'] ?? null, 'w780'),
                    'large' => $this->tmdbService->getBackdropUrl($movie['backdrop_path'] ?? null, 'w1280'),
                    'original' => $this->tmdbService->getBackdropUrl($movie['backdrop_path'] ?? null, 'original'),
                ]
            ];

            // * Adiciona URLs para logos das produtoras se existirem
            if (isset($movie['production_companies'])) {
                foreach ($movie['production_companies'] as &$company) {
                    if ($company['logo_path']) {
                        $company['logo_url'] = $this->tmdbService->getLogoUrl($company['logo_path']);
                    }
                }
            }

            // * Adiciona URLs para posters da coleção se existir
            if (isset($movie['belongs_to_collection']['poster_path'])) {
                $movie['belongs_to_collection']['poster_url'] = $this->tmdbService->getPosterUrl(
                    $movie['belongs_to_collection']['poster_path']
                );
                $movie['belongs_to_collection']['backdrop_url'] = $this->tmdbService->getBackdropUrl(
                    $movie['belongs_to_collection']['backdrop_path'] ?? null
                );
            }

            //? Retorna detalhes do filme
            return response()->json($movie);
        } catch (\Exception $e) {
            //! [ERROR] Captura e retorna erro interno
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * * Endpoint específico para buscar URLs de imagens de um filme
     * @param int $movieId
     * @return JsonResponse
     */
    public function getMovieImages(int $movieId): JsonResponse
    {
        try {
            $movie = $this->tmdbService->getMovieDetails($movieId);

            //! [ERROR] Retorna erro se o filme não for encontrado
            if (!$movie) {
                return response()->json(['error' => 'Filme não encontrado'], 404);
            }

            $images = [
                'movie_id' => $movieId,
                'title' => $movie['title'] ?? 'Título não disponível',
                'poster_urls' => $this->tmdbService->getImageUrls($movie['poster_path'] ?? null, 'poster'),
                'backdrop_urls' => $this->tmdbService->getImageUrls($movie['backdrop_path'] ?? null, 'backdrop'),
            ];

            // * Adiciona URLs da coleção se existir
            if (isset($movie['belongs_to_collection'])) {
                $images['collection'] = [
                    'name' => $movie['belongs_to_collection']['name'],
                    'poster_urls' => $this->tmdbService->getImageUrls(
                        $movie['belongs_to_collection']['poster_path'] ?? null, 
                        'poster'
                    ),
                    'backdrop_urls' => $this->tmdbService->getImageUrls(
                        $movie['belongs_to_collection']['backdrop_path'] ?? null, 
                        'backdrop'
                    ),
                ];
            }

            return response()->json($images);
        } catch (\Exception $e) {
            //! [ERROR] Captura e retorna erro interno
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
            
            $movies = $this->tmdbService->getTrendingMovies($timeWindow, $page);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao buscar filmes em alta'], 500);
            }

            return response()->json([
                'success' => true,
                'time_window' => $timeWindow,
                'data' => $movies['results'] ?? [],
                'pagination' => $this->tmdbService->getPaginationInfo($movies)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * * Descobre filmes com filtros avançados
     * @param int $page
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

            $movies = $this->tmdbService->discoverMovies($filters, $page);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao descobrir filmes'], 500);
            }

            return response()->json([
                'success' => true,
                'filters' => $filters,
                'data' => $movies['results'] ?? [],
                'pagination' => $this->tmdbService->getPaginationInfo($movies)
            ]);
        } catch (\Exception $e) {
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
            
            $additionalFilters = [
                'sort_by' => $sortBy
            ];

            $movies = $this->tmdbService->getMoviesByGenre($genreId, $page, $additionalFilters);

            if (!$movies) {
                return response()->json(['error' => 'Falha ao buscar filmes do gênero'], 500);
            }

            $genre = $this->tmdbService->getGenreById($genreId);

            return response()->json([
                'success' => true,
                'genre' => $genre,
                'sort_by' => $sortBy,
                'data' => $movies['results'] ?? [],
                'pagination' => $this->tmdbService->getPaginationInfo($movies)
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     ** Busca múltiplos filmes por IDs
     * @param array $movieIds
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
            $movies = $this->tmdbService->getMoviesByIds($movieIds);

            return response()->json([
                'success' => true,
                'requested_ids' => $movieIds,
                'found_movies' => count($movies),
                'data' => $movies
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
