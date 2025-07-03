<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\TmdbService;
use App\Services\Tmdb\TmdbMovieService;
use App\Services\Tmdb\TmdbGenreService;
use App\Services\Tmdb\TmdbSearchService;
use App\Services\Tmdb\TmdbImageService;
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
        $this->app->bind(TmdbImageServiceInterface::class, TmdbImageService::class);
        $this->app->bind(TmdbGenreServiceInterface::class, TmdbGenreService::class);
        
        $this->app->bind(TmdbSearchServiceInterface::class, function ($app) {
            return new TmdbSearchService(
                $app->make(TmdbGenreServiceInterface::class)
            );
        });

        $this->app->bind(TmdbMovieServiceInterface::class, function ($app) {
            return new TmdbMovieService(
                $app->make(TmdbGenreServiceInterface::class)
            );
        });

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
        //
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
        ];
    }
}
