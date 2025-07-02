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
     * * Busca filmes populares da TMDB
     * @param int $page
     * @return JsonResponse
     */
    public function getPopularMovies(int $page = 1): JsonResponse
    {
        try {
            $movies = $this->tmdbService->getPopularMovies($page);

            //! [ERROR] Trata caso não retorne filmes ou retorne erro da API
            if (!$movies || isset($movies['status_code'])) {
                $errorMsg = 'Falha ao buscar filmes populares';
                if (isset($movies['status_message'])) {
                    $errorMsg .= ': ' . $movies['status_message'];
                }
                return response()->json(['error' => $errorMsg], 500);
            }

            //? Retorna filmes populares
            return response()->json($movies);
        } catch (\Exception $e) {
            //! [ERROR] Captura e retorna erro interno
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * * Realiza busca de filmes na TMDB
     * @return JsonResponse
     */
    public function searchMovies(): JsonResponse
    {
        $query = request('query');
        $page = request('page', 1);

        //! [ERROR] Query obrigatória para busca
        if (!$query) {
            return response()->json(['error' => 'Query é obrigatória'], 400);
        }

        try {
            $results = $this->tmdbService->searchMovies($query, $page);

            //* Trata caso não retorne resultados ou retorne erro da API
            if (!$results || isset($results['status_code'])) {
                $errorMsg = 'Falha na busca de filmes';
                if (isset($results['status_message'])) {
                    $errorMsg .= ': ' . $results['status_message'];
                }
                return response()->json(['error' => $errorMsg], 500);
            }

            //? Retorna resultados da busca
            return response()->json($results);
        } catch (\Exception $e) {
            //! [ERROR] Captura e retorna erro interno
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

            //? Retorna detalhes do filme
            return response()->json($movie);
        } catch (\Exception $e) {
            //! [ERROR] Captura e retorna erro interno
            return response()->json([
                'error' => 'Erro interno: ' . $e->getMessage()
            ], 500);
        }
    }
}
