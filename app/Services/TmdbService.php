<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;

class TmdbService
{
    protected PendingRequest $client;
    // TODO: Verificar os valores de cache e rate limit
    protected int $cacheTimeout = 60; //? 1 minuto
    protected int $rateLimitDelay = 250; //? 250ms entre requests
    protected static ?float $lastRequestTime = null;

    public function __construct()
    {
        $apiKey = config('services.tmdb.api_key');

        if (!$apiKey) {
            // ! [ERROR] Chave API TMDB não configurada no .env
            Log::error('Chave API TMDB não configurada no .env');
            abort(500, 'TMDB API Key não configurada no .env');
        }

        $this->client = Http::withToken($apiKey)
            ->baseUrl('https://api.themoviedb.org/3')
            ->acceptJson()
            ->timeout(30)
            ->retry(3, 1000)
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
     * * Executa uma requisição com cache e tratamento de erros
     * @param string $endpoint
     * @param array $params
     * @param bool $useCache
     * @return ?array
     */
    protected function makeRequest(string $endpoint, array $params = [], bool $useCache = true): ?array
    {
        $cacheKey = $this->generateCacheKey($endpoint, $params);

        if ($useCache && Cache::has($cacheKey)) {
            // * Retorna do cache se existir
            return Cache::get($cacheKey);
        }

        $this->enforceRateLimit();

        try {
            $response = $this->client->get($endpoint, $params);

            if ($response->successful()) {
                $data = $response->json();

                if ($useCache) {
                    // * Salva no cache
                    Cache::put($cacheKey, $data, $this->cacheTimeout);
                }

                return $data;
            }

            //! [ERROR] Falha na requisição
            $this->logError($response, $endpoint, $params);
            return null;
        } catch (\Exception $e) {
            //! [ERROR] Exceção na requisição TMDB
            Log::error('Erro na requisição TMDB', [
                'endpoint' => $endpoint,
                'params' => $params,
                'error' => $e->getMessage()
            ]);

            return null;
        }
    }

    /**
     * * Gera chave de cache única para a requisição
     * @param string $endpoint
     * @param array $params
     * @return string
     */
    protected function generateCacheKey(string $endpoint, array $params = []): string
    {
        return 'tmdb_' . md5($endpoint . serialize($params));
    }

    /**
     * * Log de erros detalhado
     * @param Response $response
     * @param string $endpoint
     * @param array $params
     * @return void
     */
    protected function logError(Response $response, string $endpoint, array $params = []): void
    {
        Log::error('Falha na requisição TMDB', [
            'endpoint' => $endpoint,
            'params' => $params,
            'status_code' => $response->status(),
            'response' => $response->body(),
            'headers' => $response->headers()
        ]);
    }

    /**
     * * Busca detalhes da conta
     * @param string $accountId
     * @return ?array
     */
    public function getAccountDetails(string $accountId): ?array
    {
        return $this->makeRequest("/account/{$accountId}");
    }

    /**
     * * Busca filmes populares
     * @param int $page
     * @return ?array
     */
    public function getPopularMovies(int $page = 1): ?array
    {
        return $this->makeRequest('/movie/popular', ['page' => $page]);
    }

    /**
     * * Busca filmes em cartaz
     * @param int $page
     * @return ?array
     */
    public function getNowPlayingMovies(int $page = 1): ?array
    {
        return $this->makeRequest('/movie/now_playing', ['page' => $page]);
    }

    /**
     * * Busca filmes mais bem avaliados
     * @param int $page
     * @return ?array
     */
    public function getTopRatedMovies(int $page = 1): ?array
    {
        return $this->makeRequest('/movie/top_rated', ['page' => $page]);
    }

    /**
     * * Busca próximos lançamentos
     * @param int $page
     * @return ?array
     */
    public function getUpcomingMovies(int $page = 1): ?array
    {
        return $this->makeRequest('/movie/upcoming', ['page' => $page]);
    }

    /**
     * * Busca detalhes de um filme específico
     * @param int $movieId
     * @param array $appendTo
     * @return ?array
     */
    public function getMovieDetails(int $movieId, array $appendTo = []): ?array
    {
        $params = [];

        if (!empty($appendTo)) {
            // * Adiciona parâmetros extras à resposta
            $params['append_to_response'] = implode(',', $appendTo);
        }

        return $this->makeRequest("/movie/{$movieId}", $params);
    }

    /**
     * * Busca por filmes
     * @param string $query
     * @param int $page
     * @param array $filters
     * @return ?array
     */
    public function searchMovies(string $query, int $page = 1, array $filters = []): ?array
    {
        $params = array_merge([
            'query' => $query,
            'page' => $page
        ], $filters);

        return $this->makeRequest('/search/movie', $params);
    }

    /**
     * * Busca múltiplos filmes por IDs
     * @param array $movieIds
     * @param array $appendTo
     * @return array
     */
    public function getMoviesByIds(array $movieIds, array $appendTo = []): array
    {
        $movies = [];

        foreach ($movieIds as $movieId) {
            $movie = $this->getMovieDetails($movieId, $appendTo);

            if ($movie) {
                $movies[] = $movie;
            }
        }

        return $movies;
    }

    /**
     * * Busca trending
     * @param string $timeWindow
     * @return ?array
     */
    public function getTrendingMovies(string $timeWindow = 'day'): ?array
    {
        return $this->makeRequest("/trending/movie/{$timeWindow}");
    }

    /**
     * * Busca filmes descobertos
     * @param array $filters
     * @return ?array
     */
    public function discoverMovies(array $filters = []): ?array
    {
        return $this->makeRequest('/discover/movie', $filters);
    }

    /**
     * * Busca gêneros de filmes
     * @return ?array
     */
    public function getMovieGenres(): ?array
    {
        return $this->makeRequest('/genre/movie/list');
    }

    /**
     * * Invalida cache específico
     * @param string $endpoint
     * @param array $params
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
