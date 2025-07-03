<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;
use Exception;

class TmdbService
{
    protected PendingRequest $client;
    protected int $cacheTimeout = 300; // 5 minutos para dados dinâmicos
    protected int $staticCacheTimeout = 86400; // 24 horas para dados estáticos 
    protected int $rateLimitDelay = 250; // 250ms entre requests
    protected static ?float $lastRequestTime = null;

    public const CACHE_PREFIX = 'tmdb_';
    public const GENRES_CACHE_KEY = 'tmdb_movie_genres';
    public const DEFAULT_PAGE_SIZE = 20;

    public function __construct()
    {
        $apiKey = config('services.tmdb.api_key');
        $tmdbBaseUrl = config('services.tmdb.api_base_url', 'https://api.themoviedb.org/3');

        if (!$apiKey) {
            Log::error('TMDB API Key não configurada no .env');
            throw new RuntimeException('TMDB API Key não configurada no .env');
        }

        $this->client = Http::withToken($apiKey)
            ->baseUrl($tmdbBaseUrl)
            ->acceptJson()
            ->timeout(30)
            ->retry(3, 1000, function ($exception, $request) {
                return $exception instanceof ConnectionException;
            })
            ->withOptions([
                'force_ip_resolve' => 'v4',
                'verify' => true
            ]);
    }

    /**
     * * Rate limiting para evitar exceder os limites da API
     * @return void
     */
    protected function enforceRateLimit(): void
    {
        if (self::$lastRequestTime !== null) {
            $timeSinceLastRequest = (microtime(true) - self::$lastRequestTime) * 1000;

            if ($timeSinceLastRequest < $this->rateLimitDelay) {
                //! [ERROR] Aguarda para não exceder o limite de requisições
                usleep(($this->rateLimitDelay - $timeSinceLastRequest) * 1000);
            }
        }

        self::$lastRequestTime = microtime(true);
    }

    /**
     ** Executa uma requisição com cache
     * @param string $endpoint
     * @param array $params
     * @param bool $useCache
     * @param int|null $customCacheTimeout
     * @return array|null
     */
    protected function makeRequest(string $endpoint, array $params = [], bool $useCache = true, ?int $customCacheTimeout = null): ?array
    {
        $cacheKey = $this->generateCacheKey($endpoint, $params);
        $cacheTimeout = $customCacheTimeout ?? $this->cacheTimeout;

        if ($useCache && Cache::has($cacheKey)) {
            return Cache::get($cacheKey);
        }

        $this->enforceRateLimit();

        try {
            $response = $this->client->get($endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();

                if ($useCache) {
                    Cache::put($cacheKey, $data, $cacheTimeout);
                }

                return $data;
            }

            // ! Tratamento de erros
            $this->handleHttpError($response, $endpoint, $params);
            return null;
        } catch (ConnectionException $e) {
            Log::error('TMDB Connection Error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Falha na conexão com TMDB API: ' . $e->getMessage());
        } catch (RequestException $e) {
            Log::error('TMDB Request Error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage()
            ]);
            throw new RuntimeException('Erro na requisição TMDB: ' . $e->getMessage());
        } catch (Exception $e) {
            Log::error('TMDB Unexpected Error', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            throw new RuntimeException('Erro inesperado na API TMDB: ' . $e->getMessage());
        }
    }

    /**
     ** Gera chave de cache única para a requisição
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    protected function generateCacheKey(string $endpoint, array $params = []): string
    {
        return self::CACHE_PREFIX . md5($endpoint . serialize($params));
    }

    /**
     ** Tratamento específico de erros HTTP
     * @param Response $response
     * @param string $endpoint
     * @param array $params
     */
    protected function handleHttpError(Response $response, string $endpoint, array $params = []): void
    {
        $statusCode = $response->status();
        $responseBody = $response->json();

        $errorMessage = $responseBody['status_message'] ?? 'Erro desconhecido na API TMDB';

        // ! [ERROR] Log detalhado do erro
        Log::error('TMDB API Error', [
            'endpoint' => $endpoint,
            'params' => $params,
            'status_code' => $statusCode,
            'error_message' => $errorMessage,
            'response' => $responseBody
        ]);

        switch ($statusCode) {
            case 401:
                throw new RuntimeException('Chave de API TMDB inválida ou expirada');
            case 404:
                throw new RuntimeException('Recurso não encontrado na API TMDB');
            case 429:
                throw new RuntimeException('Limite de requisições TMDB excedido. Tente novamente mais tarde.');
            case 500:
            case 502:
            case 503:
                throw new RuntimeException('Erro interno do servidor TMDB. Tente novamente mais tarde.');
            default:
                throw new RuntimeException("Erro TMDB ({$statusCode}): {$errorMessage}");
        }
    }

    /**
     * * Valida e sanitiza parâmetros de paginação
     * @param array $params
     * @return array
     */
    protected function validatePaginationParams(array $params): array
    {
        // TMDB API suporta páginas de 1 a 1000
        if (isset($params['page'])) {
            $params['page'] = max(1, min(1000, (int) $params['page']));
        }

        return $params;
    }

    /**
     * * Busca detalhes da conta apenas para debugging
     * @param string $accountId
     * @return array|null
     */
    public function getAccountDetails(string $accountId): ?array
    {
        return $this->makeRequest("/account/{$accountId}");
    }

    /**
     * * Busca lista de gêneros de filmes
     * @return mixed
     */
    public function getMovieGenres(): ?array
    {
        return $this->makeRequest('/genre/movie/list', [], true, $this->staticCacheTimeout);
    }

    /**
     * * Busca gênero específico por ID
     * @param int $genreId
     * @return array|null
     */
    public function getGenreById(int $genreId): ?array
    {
        $genres = $this->getMovieGenres();

        if (!$genres || !isset($genres['genres'])) {
            return null;
        }

        foreach ($genres['genres'] as $genre) {
            if ($genre['id'] === $genreId) {
                return $genre;
            }
        }

        return null;
    }

    /**
     * * Mapeia IDs de gêneros para nomes
     * @param array $genreIds
     * @return array
     */
    public function mapGenreIdsToNames(array $genreIds): array
    {
        $genres = $this->getMovieGenres();

        if (!$genres || !isset($genres['genres'])) {
            return [];
        }

        $genreMap = [];
        foreach ($genres['genres'] as $genre) {
            $genreMap[$genre['id']] = $genre['name'];
        }

        return array_map(fn($id) => $genreMap[$id] ?? 'Desconhecido', $genreIds);
    }

    /**
     * * Busca filmes populares com paginação
     * @param int $page
     * @return array|null
     */
    public function getPopularMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest('/movie/popular', $params);
    }

    /**
     * * Busca filmes em cartaz com paginação
     * @param int $page
     * @return array|null
     */
    public function getNowPlayingMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest('/movie/now_playing', $params);
    }

    /**
     * * Busca filmes mais bem avaliados com paginação
     * @param int $page
     * @return array|null
     */
    public function getTopRatedMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest('/movie/top_rated', $params);
    }

    /**
     * Busca próximos lançamentos com paginação
     * @param int $page
     * @return array|null
     */
    public function getUpcomingMovies(int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest('/movie/upcoming', $params);
    }

    /**
     * * Busca detalhes de um filme específico com dados completos
     * @param int $movieId
     * @param array $appendTo
     * @return array|null
     */
    public function getMovieDetails(int $movieId, array $appendTo = []): ?array
    {
        $params = [];

        $defaultAppendTo = ['credits', 'videos', 'images', 'recommendations', 'similar'];
        $appendTo = array_unique(array_merge($defaultAppendTo, $appendTo));

        if (!empty($appendTo)) {
            $params['append_to_response'] = implode(',', $appendTo);
        }

        $movie = $this->makeRequest("/movie/{$movieId}", $params);

        if ($movie) {
            //? Mais informações de gêneros se disponíveis
            $movie = $this->enrichMovieWithGenres($movie);
        }

        return $movie;
    }

    /**
     * Adsdiona informações completas de gêneros ao filme
     */
    protected function enrichMovieWithGenres(array $movie): array
    {
        if (isset($movie['genre_ids']) && !isset($movie['genres'])) {
            $genreNames = $this->mapGenreIdsToNames($movie['genre_ids']);
            $movie['genre_names'] = $genreNames;
        }

        if (isset($movie['genres'])) {
            $movie['genre_names'] = array_column($movie['genres'], 'name');
        }

        return $movie;
    }

    /**
     * Busca por filmes com paginação aprimorada e filtros
     * @param string $query
     * @param int $page
     * @param array $filters
     * @return array|null
     */
    public function searchMovies(string $query, int $page = 1, array $filters = []): ?array
    {
        $params = array_merge([
            'query' => trim($query),
            'page' => $page,
            'include_adult' => false,
        ], $filters);

        $params = $this->validatePaginationParams($params);

        $results = $this->makeRequest('/search/movie', $params);

        if ($results && isset($results['results'])) {
            $results['results'] = array_map([$this, 'enrichMovieWithGenres'], $results['results']);
        }

        return $results;
    }

    /**
     * Busca múltiplos filmes por IDs com dados completos
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
     * Busca filmes trending com paginação
     * @param string $timeWindow (day, week)
     * @return array|null
     */
    public function getTrendingMovies(string $timeWindow = 'day', int $page = 1): ?array
    {
        $params = $this->validatePaginationParams(['page' => $page]);
        return $this->makeRequest("/trending/movie/{$timeWindow}", $params);
    }

    /**
     ** Descobre filmes com filtros avançados e paginação
     * @param array $filters
     * @param int $page
     * @return array|null
     */
    public function discoverMovies(array $filters = [], int $page = 1): ?array
    {
        $params = array_merge($filters, ['page' => $page]);
        $params = $this->validatePaginationParams($params);

        return $this->makeRequest('/discover/movie', $params);
    }

    /**
     * * Busca filmes por gênero específico
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
     * * Busca informações de paginação formatadas
     * @param array $response
     * @return array
     */
    public function getPaginationInfo(array $response): array
    {
        return [
            'current_page' => $response['page'] ?? 1,
            'total_pages' => min($response['total_pages'] ?? 1, 1000),
            'total_results' => $response['total_results'] ?? 0,
            'per_page' => self::DEFAULT_PAGE_SIZE,
            'has_next_page' => ($response['page'] ?? 1) < min($response['total_pages'] ?? 1, 1000),
            'has_previous_page' => ($response['page'] ?? 1) > 1
        ];
    }

    /**
     * * Invalida cache específico
     * @param string $endpoint
     * @param array $params
     * @return void
     */
    public function invalidateCache(string $endpoint, array $params = []): void
    {
        $cacheKey = $this->generateCacheKey($endpoint, $params);
        Cache::forget($cacheKey);
    }

    /**
     * * Limpa todo o cache do TMDB
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * * Define timeout do cache
     * @param int $seconds
     * @return self
     */
    public function setCacheTimeout(int $seconds): self
    {
        $this->cacheTimeout = $seconds;
        return $this;
    }

    /**
     * * Desabilita cache para a próxima requisição
     * @return self
     */
    public function withoutCache(): self
    {
        return $this;
    }

    /**
     * * Gera URL completa para imagem do TMDB
     * @param string|null $imagePath
     * @param string $type (poster, backdrop, logo, profile)
     * @param string|null $size
     * @return string|null
     */
    public function getImageUrl(?string $imagePath, string $type = 'poster', ?string $size = null): ?string
    {
        if (!$imagePath) {
            return null;
        }

        $baseUrl = config('services.tmdb.image_base_url');
        $defaultSize = $size ?? config("services.tmdb.default_sizes.{$type}", 'w500');

        return $baseUrl . $defaultSize . $imagePath;
    }

    /**
     * * Gera URL do poster do filme
     * @param string|null $posterPath
     * @param string $size
     * @return string|null
     */
    public function getPosterUrl(?string $posterPath, string $size = 'w500'): ?string
    {
        return $this->getImageUrl($posterPath, 'poster', $size);
    }

    /**
     * * Gera URL do backdrop do filme
     * @param string|null $backdropPath
     * @param string $size
     * @return string|null
     */
    public function getBackdropUrl(?string $backdropPath, string $size = 'w1280'): ?string
    {
        return $this->getImageUrl($backdropPath, 'backdrop', $size);
    }

    /**
     * * Gera URL do logo
     * @param string|null $logoPath
     * @param string $size
     * @return string|null
     */
    public function getLogoUrl(?string $logoPath, string $size = 'w185'): ?string
    {
        return $this->getImageUrl($logoPath, 'logo', $size);
    }

    /**
     * * Gera URL do perfil (para atores/diretores)
     * @param string|null $profilePath
     * @param string $size
     * @return string|null
     */
    public function getProfileUrl(?string $profilePath, string $size = 'w185'): ?string
    {
        return $this->getImageUrl($profilePath, 'profile', $size);
    }

    /**
     * * Gera múltiplas URLs de imagem em diferentes tamanhos
     * @param string|null $imagePath
     * @param string $type
     * @param array $sizes
     * @return array
     */
    public function getImageUrls(?string $imagePath, string $type = 'poster', array $sizes = []): array
    {
        if (!$imagePath) {
            return [];
        }

        if (empty($sizes)) {
            $sizes = config("services.tmdb.image_sizes.{$type}", ['w500']);
        }

        $urls = [];
        foreach ($sizes as $size) {
            $urls[$size] = $this->getImageUrl($imagePath, $type, $size);
        }

        return $urls;
    }
}
