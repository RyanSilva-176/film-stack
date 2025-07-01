<?php

namespace App\Http\Controllers;

use App\Services\TmdbService;

class TmdbController extends Controller
{
    public function __construct(protected TmdbService $tmdbService)
    {
        if (!config('services.tmdb.api_key')) {
            abort(500, 'TMDB API Key não configurada no .env');
        }

        if (!config('services.tmdb.account_id')) {
            abort(500, 'TMDB Account ID não configurado no .env');
        }

        if (!$this->tmdbService) {
            abort(500, 'Serviço TMDB não disponível');
        }
    }

    public function testAccountDetails()
    {
        $accountId = config('services.tmdb.account_id');

        if (!$accountId) {
            return response()->json(['error' => 'TMDB Account ID não configurado no .env'], 500);
        }

        $details = $this->tmdbService->getAccountDetails($accountId);

        dd($details);
    }
}
