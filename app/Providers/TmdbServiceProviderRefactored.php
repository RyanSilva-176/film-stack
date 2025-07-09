<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Tmdb\TmdbService;

// Services originais
use App\Services\Tmdb\Services\TmdbMovieService;
use App\Services\Tmdb\Services\TmdbGenreService;
use App\Services\Tmdb\Services\TmdbSearchService;
use App\Services\Tmdb\Services\TmdbImageService;

// Services refatorados
use App\Services\Tmdb\Services\TmdbMovieServiceRefactored;
use App\Services\Tmdb\Services\TmdbSearchServiceRefactored;

// Novos componentes
use App\Services\Tmdb\Enrichers\MovieDataEnricher;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\Strategies\SimpleMovieSearchStrategy;
use App\Services\Tmdb\Strategies\GenreFilteredSearchStrategy;
use App\Services\Tmdb\Strategies\AdvancedSearchStrategy;

// Contratos
use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;

class TmdbServiceProviderRefactored extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Registra serviços base primeiro
        $this->registerBaseServices();
        
        // Registra novos componentes
        $this->registerNewComponents();
        
        // Registra strategies de busca
        $this->registerSearchStrategies();
        
        // Registra serviços principais (com flag para usar refatorados)
        $this->registerMainServices();
        
        // Registra facade principal
        $this->registerFacade();
    }

    /**
     * Registra serviços base que não foram alterados
     */
    private function registerBaseServices(): void
    {
        $this->app->bind(TmdbImageServiceInterface::class, TmdbImageService::class);
        $this->app->bind(TmdbGenreServiceInterface::class, TmdbGenreService::class);
    }

    /**
     * Registra novos componentes da refatoração
     */
    private function registerNewComponents(): void
    {
        // Enricher que precisa dos serviços base
        $this->app->bind(MovieDataEnricher::class, function ($app) {
            return new MovieDataEnricher(
                $app->make(TmdbGenreServiceInterface::class),
                $app->make(TmdbImageServiceInterface::class)
            );
        });

        // Processor que precisa do enricher
        $this->app->bind(MovieCollectionProcessor::class, function ($app) {
            return new MovieCollectionProcessor(
                $app->make(MovieDataEnricher::class)
            );
        });
    }

    /**
     * Registra strategies de busca
     */
    private function registerSearchStrategies(): void
    {
        $this->app->bind(SimpleMovieSearchStrategy::class, function ($app) {
            return new SimpleMovieSearchStrategy(
                $app->make(MovieCollectionProcessor::class)
            );
        });

        $this->app->bind(GenreFilteredSearchStrategy::class, function ($app) {
            return new GenreFilteredSearchStrategy(
                $app->make(MovieCollectionProcessor::class)
            );
        });

        $this->app->bind(AdvancedSearchStrategy::class, function ($app) {
            return new AdvancedSearchStrategy(
                $app->make(MovieCollectionProcessor::class)
            );
        });
    }

    /**
     * Registra serviços principais com opção de usar versões refatoradas
     */
    private function registerMainServices(): void
    {
        $useRefactoredServices = config('tmdb.use_refactored_services', false);

        if ($useRefactoredServices) {
            $this->registerRefactoredServices();
        } else {
            $this->registerOriginalServices();
        }
    }

    /**
     * Registra serviços refatorados
     */
    private function registerRefactoredServices(): void
    {
        $this->app->bind(TmdbMovieServiceInterface::class, function ($app) {
            return new TmdbMovieServiceRefactored(
                $app->make(MovieDataEnricher::class),
                $app->make(MovieCollectionProcessor::class)
            );
        });

        $this->app->bind(TmdbSearchServiceInterface::class, function ($app) {
            return new TmdbSearchServiceRefactored(
                $app->make(SimpleMovieSearchStrategy::class),
                $app->make(GenreFilteredSearchStrategy::class),
                $app->make(AdvancedSearchStrategy::class),
                $app->make(MovieCollectionProcessor::class)
            );
        });
    }

    /**
     * Registra serviços originais (comportamento atual)
     */
    private function registerOriginalServices(): void
    {
        $this->app->bind(TmdbSearchServiceInterface::class, function ($app) {
            return new TmdbSearchService(
                $app->make(TmdbGenreServiceInterface::class),
                $app->make(TmdbImageServiceInterface::class)
            );
        });

        $this->app->bind(TmdbMovieServiceInterface::class, function ($app) {
            return new TmdbMovieService(
                $app->make(TmdbGenreServiceInterface::class),
                $app->make(TmdbImageServiceInterface::class)
            );
        });
    }

    /**
     * Registra facade principal
     */
    private function registerFacade(): void
    {
        $this->app->bind(TmdbService::class, function ($app) {
            return new TmdbService(
                $app->make(TmdbMovieServiceInterface::class),
                $app->make(TmdbGenreServiceInterface::class),
                $app->make(TmdbSearchServiceInterface::class),
                $app->make(TmdbImageServiceInterface::class)
            );
        });

        $this->app->singleton('tmdb', function ($app) {
            return $app->make(TmdbService::class);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Publica configuração se necessário
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/tmdb.php' => config_path('tmdb.php'),
            ], 'tmdb-config');
        }
    }

    /**
     * Get the services provided by the provider.
     */
    public function provides(): array
    {
        return [
            TmdbImageServiceInterface::class,
            TmdbGenreServiceInterface::class,
            TmdbSearchServiceInterface::class,
            TmdbMovieServiceInterface::class,
            TmdbService::class,
            'tmdb',
            
            // Novos componentes
            MovieDataEnricher::class,
            MovieCollectionProcessor::class,
            SimpleMovieSearchStrategy::class,
            GenreFilteredSearchStrategy::class,
            AdvancedSearchStrategy::class,
            TmdbMovieServiceRefactored::class,
            TmdbSearchServiceRefactored::class,
        ];
    }
}
