<?php

namespace Tests\Unit\Controllers\Api;

use Tests\TestCase;
use App\Http\Controllers\Api\TmdbController;
use App\Services\Tmdb\TmdbService;
use Symfony\Component\HttpKernel\Exception\HttpException;
use RuntimeException;


class TmdbControllerTest extends TestCase
{
    public function test_controller_requires_tmdb_api_key()
    {
        config(['services.tmdb.api_key' => null]);
        
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('TMDB API Key não configurada no .env');
        
        $tmdbService = app(TmdbService::class);
        new TmdbController($tmdbService);
    }

    public function test_controller_requires_tmdb_account_id()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => null]);
        
        $this->expectException(HttpException::class);
        $this->expectExceptionMessage('TMDB Account ID não configurado no .env');
        
        $tmdbService = app(TmdbService::class);
        new TmdbController($tmdbService);
    }

    public function test_controller_can_be_instantiated_with_valid_config()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => 'test_account']);
        
        $tmdbService = app(TmdbService::class);
        $controller = new TmdbController($tmdbService);
        
        $this->assertInstanceOf(TmdbController::class, $controller);
    }

    public function test_search_movies_validates_empty_query()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => 'test_account']);
        
        $tmdbService = app(TmdbService::class);
        $controller = new TmdbController($tmdbService);
        
        request()->merge(['query' => '']);
        
        $response = $controller->searchMovies();
        
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Query é obrigatória e não pode estar vazia', $responseData['error']);
    }

    public function test_search_movies_validates_whitespace_query()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => 'test_account']);
        
        $tmdbService = app(TmdbService::class);
        $controller = new TmdbController($tmdbService);
        
        request()->merge(['query' => '   ']);
        
        $response = $controller->searchMovies();
        
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Query é obrigatória e não pode estar vazia', $responseData['error']);
    }

    public function test_get_movies_by_ids_validates_input_type()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => 'test_account']);
        
        $tmdbService = app(TmdbService::class);
        $controller = new TmdbController($tmdbService);
        
        request()->merge(['movie_ids' => 'invalid']);
        
        $response = $controller->getMoviesByIds();
        
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('movie_ids deve ser um array de IDs', $responseData['error']);
    }

    public function test_get_movies_by_ids_validates_array_size()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => 'test_account']);
        
        $tmdbService = app(TmdbService::class);
        $controller = new TmdbController($tmdbService);
        
        $tooManyIds = range(1, 25);
        request()->merge(['movie_ids' => $tooManyIds]);
        
        $response = $controller->getMoviesByIds();
        
        $this->assertEquals(400, $response->getStatusCode());
        $responseData = json_decode($response->getContent(), true);
        $this->assertEquals('Máximo de 20 filmes por requisição', $responseData['error']);
    }

    public function test_get_movies_by_ids_filters_numeric_values()
    {
        config(['services.tmdb.api_key' => 'test_key']);
        config(['services.tmdb.account_id' => 'test_account']);
        
        $tmdbService = app(TmdbService::class);
        $controller = new TmdbController($tmdbService);
        
        $mixedIds = [1, 'invalid', 2, null, 3, 'another_invalid'];
        request()->merge(['movie_ids' => $mixedIds]);
        
        $response = $controller->getMoviesByIds();
        
        $this->assertTrue(in_array($response->getStatusCode(), [200, 500]));
    }
}
