<?php

namespace App\Services\Tmdb\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array|null getPopularMovies(int $page = 1)
 * @method static array|null getNowPlayingMovies(int $page = 1)
 * @method static array|null getTopRatedMovies(int $page = 1)
 * @method static array|null getUpcomingMovies(int $page = 1)
 * @method static \App\Services\Tmdb\DTOs\MovieDTO|null getMovieDetails(int $movieId, array $appendTo = [])
 * @method static array getMoviesByIds(array $movieIds, array $appendTo = [])
 * @method static array|null getTrendingMovies(string $timeWindow = 'day', int $page = 1)
 * @method static array|null discoverMovies(array $filters = [], int $page = 1)
 * @method static array|null getMoviesByGenre(int $genreId, int $page = 1, array $additionalFilters = [])
 * @method static array|null getMovieGenres()
 * @method static \App\Services\Tmdb\DTOs\GenreDTO|null getGenreById(int $genreId)
 * @method static array mapGenreIdsToNames(array $genreIds)
 * @method static \App\Services\Tmdb\DTOs\SearchResultDTO|null searchMovies(string $query, int $page = 1, array $filters = [])
 * @method static \App\Services\Tmdb\DTOs\SearchResultDTO|null searchMulti(string $query, int $page = 1, array $filters = [])
 * @method static \App\Services\Tmdb\DTOs\SearchResultDTO|null searchPeople(string $query, int $page = 1, array $filters = [])
 * @method static array searchWithSuggestions(string $query, int $page = 1)
 * @method static string|null getImageUrl(?string $imagePath, string $type = 'poster', ?string $size = null)
 * @method static string|null getPosterUrl(?string $posterPath, string $size = 'w500')
 * @method static string|null getBackdropUrl(?string $backdropPath, string $size = 'w1280')
 * @method static array getImageUrls(?string $imagePath, string $type = 'poster', array $sizes = [])
 * @method static array getPaginationInfo(array $response)
 * @method static void clearAllCache()
 * @method static self setCacheTimeout(int $seconds)
 * @method static array|null getMovieForDisplay(int $movieId)
 * @method static array|null getMoviesWithImages(string $listType = 'popular', int $page = 1)
 * @method static array|null searchWithImages(string $query, int $page = 1)
 *
 * @see \App\Services\Tmdb\TmdbService
 */
class Tmdb extends Facade
{
    /**
     * Get the registered name of the component.
     */
    protected static function getFacadeAccessor(): string
    {
        return 'tmdb';
    }
}
