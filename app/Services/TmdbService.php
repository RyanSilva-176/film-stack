<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Facades\Log;

class TmdbService
{
    protected PendingRequest $client;

    public function __construct()
    {
        $apiKey = config('services.tmdb.api_key');

        if (!$apiKey) {
            Log::error('Chave API TMDB não configurada no .env');
            abort(500, 'TMDB API Key não configurada no .env');
        }

        $this->client = Http::withToken($apiKey)
            ->baseUrl('https://api.themoviedb.org/3')
            ->acceptJson()
            ->withOptions(['force_ip_resolve' => 'v4']);
    }

    /**
     * @param string $accountId 
     * @return array|null
     */
    public function getAccountDetails(string $accountId): ?array
    {
        $response = $this->client->get("/account/{$accountId}");

        if ($response->successful()) {
            return $response->json();
        }

        Log::error('Falha ao buscar detalhes da conta TMDB', ['response' => $response->body()]);

        return null;
    }
}
