<?php

namespace App\Services\Tmdb;

use App\Services\Tmdb\Contracts\TmdbServiceInterface;
use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Http\Client\Response;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use RuntimeException;
use Exception;

abstract class TmdbBaseService implements TmdbServiceInterface
{
    protected PendingRequest $client;
    protected int $cacheTimeout = 300; // 5 minutos para dados dinâmicos
    protected int $staticCacheTimeout = 86400; // 24 horas para dados estáticos
    protected int $rateLimitDelay = 250; // 250ms entre requests
    protected static ?float $lastRequestTime = null;

    public const CACHE_PREFIX = 'tmdb_';
    public const DEFAULT_PAGE_SIZE = 20;

    public function __construct()
    {
        $this->initializeClient();
    }

    /**
     ** Inicializa o cliente HTTP com configurações do TMDB
     */
    protected function initializeClient(): void
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
     ** Rate limiting para evitar exceder os limites da API
     */
    protected function enforceRateLimit(): void
    {
        if (self::$lastRequestTime !== null) {
            $timeSinceLastRequest = (microtime(true) - self::$lastRequestTime) * 1000;

            if ($timeSinceLastRequest < $this->rateLimitDelay) {
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
    public function makeRequest(string $endpoint, array $params = [], bool $useCache = true, ?int $customCacheTimeout = null): ?array
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
    public function generateCacheKey(string $endpoint, array $params = []): string
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
     ** Valida e sanitiza parâmetros de paginação
     * @param array $params
     * @return array
     */
    protected function validatePaginationParams(array $params): array
    {
        if (isset($params['page'])) {
            $params['page'] = max(1, min(1000, (int) $params['page']));
        }

        return $params;
    }

    /**
     ** Invalida cache específico
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
     ** Limpa todo o cache do TMDB
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     ** Define timeout do cache
     * @param int $seconds
     */
    public function setCacheTimeout(int $seconds): self
    {
        $this->cacheTimeout = $seconds;
        return $this;
    }

    /**
     ** Busca informações de paginação formatadas
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
}
