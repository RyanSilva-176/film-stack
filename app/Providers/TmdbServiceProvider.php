<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\Tmdb\TmdbService;

//* Services principais
use App\Services\Tmdb\Services\TmdbMovieService;
use App\Services\Tmdb\Services\TmdbSearchService;

//* Services que não mudaram
use App\Services\Tmdb\Services\TmdbGenreService;
use App\Services\Tmdb\Services\TmdbImageService;

//* Novos componentes
use App\Services\Tmdb\Enrichers\MovieDataEnricher;
use App\Services\Tmdb\Processors\MovieCollectionProcessor;
use App\Services\Tmdb\Strategies\SimpleMovieSearchStrategy;
use App\Services\Tmdb\Strategies\GenreFilteredSearchStrategy;
use App\Services\Tmdb\Strategies\AdvancedSearchStrategy;

//* Contratos
use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;

class TmdbServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $this->registerBaseServices();

        $this->registerNewComponents();

        $this->registerSearchStrategies();

        $this->registerMainServices();

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
        $this->app->bind(MovieDataEnricher::class, function ($app) {
            return new MovieDataEnricher(
                $app->make(TmdbGenreServiceInterface::class),
                $app->make(TmdbImageServiceInterface::class)
            );
        });

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
     * Registra serviços principais
     */
    private function registerMainServices(): void
    {
        $this->app->bind(TmdbMovieServiceInterface::class, function ($app) {
            return new TmdbMovieService(
                $app->make(MovieDataEnricher::class),
                $app->make(MovieCollectionProcessor::class)
            );
        });

        $this->app->bind(TmdbSearchServiceInterface::class, function ($app) {
            return new TmdbSearchService(
                $app->make(SimpleMovieSearchStrategy::class),
                $app->make(GenreFilteredSearchStrategy::class),
                $app->make(AdvancedSearchStrategy::class),
                $app->make(MovieCollectionProcessor::class)
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
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__ . '/../../config/tmdb.php' => config_path('tmdb.php'),
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

            //* Novos componentes
            MovieDataEnricher::class,
            MovieCollectionProcessor::class,
            SimpleMovieSearchStrategy::class,
            GenreFilteredSearchStrategy::class,
            AdvancedSearchStrategy::class,
            TmdbMovieService::class,
            TmdbSearchService::class,
        ];
    }
}
