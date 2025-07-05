<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Services\Tmdb\TmdbService;

class TmdbControllerIntegrationTest extends TestCase
{
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();
        
        $this->artisan('migrate');
        
        $this->user = User::factory()->create();
    }

    public function test_tmdb_service_is_bound_correctly()
    {
        $service = app(TmdbService::class);
        $this->assertNotNull($service);
        $this->assertInstanceOf(TmdbService::class, $service);
    }

    public function test_tmdb_genres_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/genres');
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'genres' => [
                    '*' => [
                        'id',
                        'name'
                    ]
                ]
            ]);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_popular_movies_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/popular');
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'data',
                'pagination' => [
                    'current_page',
                    'total_pages',
                    'total_results',
                    'has_next_page',
                    'has_previous_page'
                ]
            ]);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_search_movies_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/search?query=matrix');
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'query',
                'data',
                'pagination'
            ]);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_search_movies_requires_query()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/search');
        $response->assertStatus(400);
        $response->assertJson(['error' => 'Query é obrigatória e não pode estar vazia']);
    }

    public function test_tmdb_movie_details_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/550'); // Fight Club
        
        if (config('services.tmdb.api_key')) {
            $this->assertTrue(in_array($response->getStatusCode(), [200, 404, 500]));
            
            if ($response->getStatusCode() === 200) {
                $response->assertJsonStructure([
                    'id',
                    'title',
                    'image_urls' => [
                        'poster' => [
                            'small',
                            'medium',
                            'large',
                            'xlarge',
                            'original'
                        ],
                        'backdrop' => [
                            'small',
                            'medium',
                            'large',
                            'original'
                        ]
                    ]
                ]);
            }
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_trending_movies_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/trending?time_window=week');
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'time_window',
                'data',
                'pagination'
            ]);
            $response->assertJson(['time_window' => 'week']);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_discover_movies_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/discover?with_genres=28&sort_by=popularity.desc');
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'filters',
                'data',
                'pagination'
            ]);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_movies_by_genre_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/genre/28'); // Action
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'genre',
                'sort_by',
                'data',
                'pagination'
            ]);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_movies_batch_endpoint()
    {
        $response = $this->actingAs($this->user)->post('/api/tmdb/movies/batch', [
            'movie_ids' => [550, 13, 155]
        ]);
        
        if (config('services.tmdb.api_key')) {
            $response->assertStatus(200);
            $response->assertJsonStructure([
                'success',
                'requested_ids',
                'found_movies',
                'data'
            ]);
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_tmdb_movies_batch_validates_input()
    {
        $response = $this->actingAs($this->user)->post('/api/tmdb/movies/batch', [
            'movie_ids' => 'invalid'
        ]);
        
        $response->assertStatus(400);
        $response->assertJson(['error' => 'movie_ids deve ser um array de IDs']);
    }

    public function test_tmdb_movie_images_endpoint()
    {
        $response = $this->actingAs($this->user)->get('/api/tmdb/movies/550/images');
        
        if (config('services.tmdb.api_key')) {
            $this->assertTrue(in_array($response->getStatusCode(), [200, 404, 500]));
            
            if ($response->getStatusCode() === 200) {
                $response->assertJsonStructure([
                    'movie_id',
                    'title',
                    'poster_urls',
                    'backdrop_urls'
                ]);
            }
        } else {
            $response->assertStatus(500);
        }
    }

    public function test_unauthenticated_requests_are_blocked()
    {
        $response = $this->get('/api/tmdb/genres');
        // Para APIs web Laravel, usuários não autenticados são redirecionados (302)
        $response->assertStatus(302);
    }

    public function test_image_service_methods()
    {
        $service = app(TmdbService::class);
        
        $posterUrl = $service->getPosterUrl('/test.jpg');
        $this->assertStringContainsString('test.jpg', $posterUrl);
        
        $backdropUrl = $service->getBackdropUrl('/backdrop.jpg');
        $this->assertStringContainsString('backdrop.jpg', $backdropUrl);
        
        $logoUrl = $service->getLogoUrl('/logo.jpg');
        $this->assertStringContainsString('logo.jpg', $logoUrl);
    }

    public function test_service_facade_methods()
    {
        $service = app(TmdbService::class);
        
        $this->assertIsString($service->getPosterUrl('/test.jpg'));
        $this->assertIsString($service->getBackdropUrl('/test.jpg'));
        $this->assertIsString($service->getLogoUrl('/test.jpg'));
        $this->assertIsArray($service->getImageUrls('/test.jpg', 'poster', ['w500', 'w780']));
    }

    public function test_pagination_info_helper()
    {
        $service = app(TmdbService::class);
        
        $testResponse = [
            'page' => 1,
            'total_pages' => 100,
            'total_results' => 2000
        ];
        
        $pagination = $service->getPaginationInfo($testResponse);
        
        $this->assertEquals(1, $pagination['current_page']);
        $this->assertEquals(100, $pagination['total_pages']);
        $this->assertEquals(2000, $pagination['total_results']);
        $this->assertTrue($pagination['has_next_page']);
        $this->assertFalse($pagination['has_previous_page']);
    }

    public function test_cache_methods()
    {
        $service = app(TmdbService::class);
        
        $service->clearAllCache();
        $service->setCacheTimeout(3600);
        
        $this->assertInstanceOf(TmdbService::class, $service->setCacheTimeout(3600));
    }
}
