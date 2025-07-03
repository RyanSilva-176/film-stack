<?php

namespace App\Services\Tmdb\Contracts;

interface TmdbServiceInterface
{
    /**
     * Executa uma requisição com cache
     */
    public function makeRequest(string $endpoint, array $params = [], bool $useCache = true, ?int $customCacheTimeout = null): ?array;

    /**
     * Gera chave de cache única para a requisição
     */
    public function generateCacheKey(string $endpoint, array $params = []): string;

    /**
     * Invalida cache específico
     */
    public function invalidateCache(string $endpoint, array $params = []): void;

    /**
     * Limpa todo o cache do TMDB
     */
    public function clearAllCache(): void;

    /**
     * Busca informações de paginação formatadas
     */
    public function getPaginationInfo(array $response): array;
}
