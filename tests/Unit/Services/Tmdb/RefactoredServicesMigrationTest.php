<?php

namespace Tests\Unit\Services\Tmdb;

use Tests\TestCase;
use App\Services\Tmdb\TmdbService;
use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\Services\TmdbMovieService;
use App\Services\Tmdb\Services\TmdbSearchService;
use App\Services\Tmdb\Enrichers\MovieDataEnricher;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\Strategies\SimpleMovieSearchStrategy;

class RefactoredServicesMigrationTest extends TestCase
{
    public function test_tmdb_facade_resolves_correctly(): void
    {
        $tmdbService = app('tmdb');
        
        $this->assertInstanceOf(TmdbService::class, $tmdbService);
    }

    public function test_movie_service_resolves_to_refactored_version(): void
    {
        $movieService = app(TmdbMovieServiceInterface::class);
        
        $this->assertInstanceOf(TmdbMovieService::class, $movieService);
    }

    public function test_search_service_resolves_to_refactored_version(): void
    {
        $searchService = app(TmdbSearchServiceInterface::class);
        
        $this->assertInstanceOf(TmdbSearchService::class, $searchService);
    }

    public function test_new_components_are_resolvable(): void
    {
        $enricher = app(MovieDataEnricher::class);
        $processor = app(MovieCollectionProcessor::class);
        $strategy = app(SimpleMovieSearchStrategy::class);

        $this->assertInstanceOf(MovieDataEnricher::class, $enricher);
        $this->assertInstanceOf(MovieCollectionProcessor::class, $processor);
        $this->assertInstanceOf(SimpleMovieSearchStrategy::class, $strategy);
    }

    public function test_dependency_injection_works_correctly(): void
    {
        $movieService = app(TmdbMovieServiceInterface::class);
    
        $this->assertInstanceOf(TmdbMovieService::class, $movieService);
        
        $this->assertTrue(method_exists($movieService, 'getPopularMovies'));
        $this->assertTrue(method_exists($movieService, 'getMovieDetails'));
    }

    public function test_facade_methods_still_exist(): void
    {
        $tmdbService = app('tmdb');
        
        $this->assertTrue(method_exists($tmdbService, 'getPopularMovies'));
        $this->assertTrue(method_exists($tmdbService, 'searchMovies'));
        $this->assertTrue(method_exists($tmdbService, 'getMovieDetails'));
        $this->assertTrue(method_exists($tmdbService, 'getTrendingMovies'));
    }

    public function test_backward_compatibility_maintained(): void
    {
        $tmdbService = app('tmdb');
        
        $reflection = new \ReflectionClass($tmdbService);
        $methods = $reflection->getMethods(\ReflectionMethod::IS_PUBLIC);
        
        $methodNames = array_map(fn($method) => $method->getName(), $methods);
        
        $essentialMethods = [
            'getPopularMovies',
            'getNowPlayingMovies', 
            'getTopRatedMovies',
            'getUpcomingMovies',
            'getTrendingMovies',
            'getMovieDetails',
            'searchMovies',
            'discoverMovies'
        ];
        
        foreach ($essentialMethods as $method) {
            $this->assertContains($method, $methodNames, "Método {$method} deve existir para compatibilidade");
        }
    }
}
