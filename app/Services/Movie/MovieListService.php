<?php

namespace App\Services\Movie;

use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\User;
use App\Services\Movie\Contracts\MovieListServiceInterface;
use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\TmdbService;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class MovieListService implements MovieListServiceInterface
{
    public function __construct(
        protected TmdbMovieServiceInterface $tmdbMovieService,
        protected TmdbService $tmdbService
    ) {}

    /**
     * Cria as listas padrão para um usuário
     */
    public function createDefaultListsForUser(User $user): void
    {
        foreach (MovieList::DEFAULT_TYPES as $type => $name) {
            MovieList::firstOrCreate([
                'user_id' => $user->id,
                'type' => $type,
            ], [
                'name' => $name,
                'description' => "Lista padrão: {$name}",
                'is_public' => false,
                'sort_order' => array_search($type, array_keys(MovieList::DEFAULT_TYPES)),
            ]);
        }
    }

    /**
     * Busca todas as listas de um usuário
     */
    public function getUserLists(User $user): Collection
    {
        return $user->movieLists()->with('items')->get();
    }

    /**
     * Cria uma lista personalizada
     */
    public function createCustomList(User $user, string $name, ?string $description = null, bool $isPublic = false): MovieList
    {
        $maxSortOrder = $user->movieLists()->max('sort_order') ?? 0;

        return MovieList::create([
            'user_id' => $user->id,
            'name' => $name,
            'type' => MovieList::TYPE_CUSTOM,
            'description' => $description,
            'is_public' => $isPublic,
            'sort_order' => $maxSortOrder + 1,
        ]);
    }

    /**
     * Adiciona filme a uma lista
     */
    public function addMovieToList(MovieList $list, int $tmdbMovieId, ?array $metadata = null): MovieListItem
    {
        $existingItem = $list->items()->where('tmdb_movie_id', $tmdbMovieId)->first();

        if ($existingItem) {
            return $existingItem;
        }

        $maxSortOrder = $list->items()->max('sort_order') ?? 0;

        $data = [
            'movie_list_id' => $list->id,
            'tmdb_movie_id' => $tmdbMovieId,
            'sort_order' => $maxSortOrder + 1,
        ];

        if ($metadata) {
            if (isset($metadata['rating'])) {
                $data['rating'] = $metadata['rating'];
            }
            if (isset($metadata['notes'])) {
                $data['notes'] = $metadata['notes'];
            }
            if (isset($metadata['watched_at'])) {
                $data['watched_at'] = $metadata['watched_at'];
            }
        }

        return MovieListItem::create($data);
    }

    /**
     * Remove filme de uma lista
     */
    public function removeMovieFromList(MovieList $list, int $tmdbMovieId): bool
    {
        return $list->items()->where('tmdb_movie_id', $tmdbMovieId)->delete() > 0;
    }

    /**
     * Verifica se filme está em uma lista
     */
    public function isMovieInList(MovieList $list, int $tmdbMovieId): bool
    {
        return $list->items()->where('tmdb_movie_id', $tmdbMovieId)->exists();
    }

    /**
     * Busca filmes de uma lista com dados do TMDB
     */
    public function getListMoviesWithDetails(MovieList $list, int $page = 1, ?array $filters = null): array
    {
        $filters = $filters ?? [];
        $perPage = $filters['per_page'] ?? 20;

        $needsFiltering = !empty($filters['search']) || !empty($filters['genre']);

        if ($needsFiltering) {
            return $this->getFilteredListMovies($list, $page, $perPage, $filters);
        }

        return $this->getSortedListMovies($list, $page, $perPage, $filters);
    }

    private function getSortedListMovies(MovieList $list, int $page, int $perPage, array $filters): array
    {
        $offset = ($page - 1) * $perPage;

        $query = $list->items();

        $sortBy = $filters['sort'] ?? 'added_date_desc';
        switch ($sortBy) {
            case 'added_date_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'added_date_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'watched_date_desc':
                $query->orderBy('watched_at', 'desc');
                break;
            case 'watched_date_asc':
                $query->orderBy('watched_at', 'asc');
                break;
            default:
                $query->orderBy('sort_order');
                break;
        }

        $items = $query->skip($offset)->take($perPage)->get();
        $movieIds = $items->pluck('tmdb_movie_id')->toArray();

        if (empty($movieIds)) {
            return [
                'movies' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 0,
                    'total_count' => 0,
                    'per_page' => $perPage,
                ]
            ];
        }

        $tmdbMovies = $this->tmdbMovieService->getMoviesByIds($movieIds);

        $movies = [];
        foreach ($items as $item) {
            $tmdbData = collect($tmdbMovies)->firstWhere('id', $item->tmdb_movie_id);

            if ($tmdbData) {
                $movieData = $tmdbData->toArray();

                $movieData['poster_url'] = $this->tmdbService->getPosterUrl($movieData['poster_path'] ?? null);
                $movieData['backdrop_url'] = $this->tmdbService->getBackdropUrl($movieData['backdrop_path'] ?? null);

                $movies[] = [
                    'id' => $item->id,
                    'movie_list_id' => $item->movie_list_id,
                    'tmdb_movie_id' => $item->tmdb_movie_id,
                    'watched_at' => $item->watched_at,
                    'rating' => $item->rating,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                    'created_at' => $item->created_at->toISOString(),
                    'updated_at' => $item->updated_at->toISOString(),
                    'movie' => $movieData,
                ];
            }
        }

        if (in_array($sortBy, ['title_asc', 'title_desc', 'release_date_asc', 'release_date_desc', 'rating_asc', 'rating_desc'])) {
            usort($movies, function ($a, $b) use ($sortBy) {
                switch ($sortBy) {
                    case 'title_asc':
                        return strcasecmp($a['movie']['title'] ?? '', $b['movie']['title'] ?? '');
                    case 'title_desc':
                        return strcasecmp($b['movie']['title'] ?? '', $a['movie']['title'] ?? '');
                    case 'release_date_asc':
                        return ($a['movie']['release_date'] ?? '') <=> ($b['movie']['release_date'] ?? '');
                    case 'release_date_desc':
                        return ($b['movie']['release_date'] ?? '') <=> ($a['movie']['release_date'] ?? '');
                    case 'rating_asc':
                        return ($a['movie']['vote_average'] ?? 0) <=> ($b['movie']['vote_average'] ?? 0);
                    case 'rating_desc':
                        return ($b['movie']['vote_average'] ?? 0) <=> ($a['movie']['vote_average'] ?? 0);
                    default:
                        return 0;
                }
            });
        }

        $totalItems = $list->items()->count();
        $totalPages = ceil($totalItems / $perPage);

        return [
            'movies' => $movies,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_count' => $totalItems,
                'per_page' => $perPage,
            ]
        ];
    }

    private function getFilteredListMovies(MovieList $list, int $page, int $perPage, array $filters): array
    {
        $sortBy = $filters['sort'] ?? 'added_date_desc';

        $query = $list->items();

        switch ($sortBy) {
            case 'added_date_desc':
                $query->orderBy('created_at', 'desc');
                break;
            case 'added_date_asc':
                $query->orderBy('created_at', 'asc');
                break;
            case 'watched_date_desc':
                $query->orderBy('watched_at', 'desc');
                break;
            case 'watched_date_asc':
                $query->orderBy('watched_at', 'asc');
                break;
            default:
                $query->orderBy('sort_order');
                break;
        }

        $allItems = $query->get();
        $movieIds = $allItems->pluck('tmdb_movie_id')->toArray();

        if (empty($movieIds)) {
            return [
                'movies' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 0,
                    'total_count' => 0,
                    'per_page' => $perPage,
                ]
            ];
        }

        $tmdbMovies = $this->tmdbMovieService->getMoviesByIds($movieIds);

        $allMovies = [];
        foreach ($allItems as $item) {
            $tmdbData = collect($tmdbMovies)->firstWhere('id', $item->tmdb_movie_id);

            if ($tmdbData) {
                $movieData = $tmdbData->toArray();

                $movieData['poster_url'] = $this->tmdbService->getPosterUrl($movieData['poster_path'] ?? null);
                $movieData['backdrop_url'] = $this->tmdbService->getBackdropUrl($movieData['backdrop_path'] ?? null);

                if (!empty($filters['search'])) {
                    $search = strtolower($filters['search']);
                    $title = strtolower($movieData['title'] ?? '');
                    $originalTitle = strtolower($movieData['original_title'] ?? '');

                    if (strpos($title, $search) === false && strpos($originalTitle, $search) === false) {
                        continue;
                    }
                }

                if (!empty($filters['genre'])) {
                    $genreIds = $movieData['genre_ids'] ?? [];
                    if (!in_array((int)$filters['genre'], $genreIds)) {
                        continue;
                    }
                }

                $allMovies[] = [
                    'id' => $item->id,
                    'movie_list_id' => $item->movie_list_id,
                    'tmdb_movie_id' => $item->tmdb_movie_id,
                    'watched_at' => $item->watched_at,
                    'rating' => $item->rating,
                    'notes' => $item->notes,
                    'sort_order' => $item->sort_order,
                    'created_at' => $item->created_at->toISOString(),
                    'updated_at' => $item->updated_at->toISOString(),
                    'movie' => $movieData,
                ];
            }
        }

        if (in_array($sortBy, ['title_asc', 'title_desc', 'release_date_asc', 'release_date_desc', 'rating_asc', 'rating_desc'])) {
            usort($allMovies, function ($a, $b) use ($sortBy) {
                switch ($sortBy) {
                    case 'title_asc':
                        return strcasecmp($a['movie']['title'] ?? '', $b['movie']['title'] ?? '');
                    case 'title_desc':
                        return strcasecmp($b['movie']['title'] ?? '', $a['movie']['title'] ?? '');
                    case 'release_date_asc':
                        return ($a['movie']['release_date'] ?? '') <=> ($b['movie']['release_date'] ?? '');
                    case 'release_date_desc':
                        return ($b['movie']['release_date'] ?? '') <=> ($a['movie']['release_date'] ?? '');
                    case 'rating_asc':
                        return ($a['movie']['vote_average'] ?? 0) <=> ($b['movie']['vote_average'] ?? 0);
                    case 'rating_desc':
                        return ($b['movie']['vote_average'] ?? 0) <=> ($a['movie']['vote_average'] ?? 0);
                    default:
                        return 0;
                }
            });
        }

        $totalFiltered = count($allMovies);
        $totalPages = ceil($totalFiltered / $perPage);
        $offset = ($page - 1) * $perPage;
        $paginatedMovies = array_slice($allMovies, $offset, $perPage);

        return [
            'movies' => $paginatedMovies,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_count' => $totalFiltered,
                'per_page' => $perPage,
            ]
        ];
    }

    /**
     * Move filme assistido da watchlist para watched
     */
    public function markMovieAsWatched(User $user, int $tmdbMovieId, ?int $rating = null, ?string $notes = null): void
    {
        DB::transaction(function () use ($user, $tmdbMovieId, $rating, $notes) {
            $watchlist = MovieList::where('user_id', $user->id)
                ->where('type', MovieList::TYPE_WATCHLIST)
                ->first();
            $watchedList = MovieList::where('user_id', $user->id)
                ->where('type', MovieList::TYPE_WATCHED)
                ->first();

            if (!$watchedList) {
                throw new Exception('Lista de filmes assistidos não encontrada');
            }

            if ($watchlist && $this->isMovieInList($watchlist, $tmdbMovieId)) {
                $this->removeMovieFromList($watchlist, $tmdbMovieId);
            }

            $metadata = [
                'watched_at' => now(),
                'rating' => $rating,
                'notes' => $notes,
            ];

            $this->addMovieToList($watchedList, $tmdbMovieId, $metadata);
        });
    }

    /**
     * Curte/descurte um filme
     */
    public function toggleMovieLike(User $user, int $tmdbMovieId): bool
    {
        $likedList = $user->getLikedMoviesList();

        if (!$likedList) {
            throw new Exception('Lista de filmes curtidos não encontrada');
        }

        if ($this->isMovieInList($likedList, $tmdbMovieId)) {
            $this->removeMovieFromList($likedList, $tmdbMovieId);
            return false;
        } else {
            $this->addMovieToList($likedList, $tmdbMovieId);
            return true;
        }
    }

    /**
     * Remove múltiplos filmes de uma lista
     */
    public function bulkRemoveMoviesFromList(MovieList $list, array $tmdbMovieIds): int
    {
        return DB::transaction(function () use ($list, $tmdbMovieIds) {
            $removedCount = $list->items()
                ->whereIn('tmdb_movie_id', $tmdbMovieIds)
                ->delete();

            return $removedCount;
        });
    }

    /**
     * Move múltiplos filmes entre listas
     */
    public function bulkMoveMoviesBetweenLists(MovieList $fromList, MovieList $toList, array $tmdbMovieIds): int
    {
        return DB::transaction(function () use ($fromList, $toList, $tmdbMovieIds) {
            $existingItems = $fromList->items()
                ->whereIn('tmdb_movie_id', $tmdbMovieIds)
                ->get();

            $movedCount = 0;

            foreach ($existingItems as $item) {
                $existsInDestination = $toList->items()
                    ->where('tmdb_movie_id', $item->tmdb_movie_id)
                    ->exists();

                if (!$existsInDestination) {
                    $toList->items()->create([
                        'tmdb_movie_id' => $item->tmdb_movie_id,
                        'watched_at' => $item->watched_at,
                        'rating' => $item->rating,
                        'notes' => $item->notes,
                        'sort_order' => $toList->items()->max('sort_order') + 1,
                    ]);

                    $movedCount++;
                }
                $item->delete();
            }

            return $movedCount;
        });
    }

    /**
     * Marca múltiplos filmes como assistidos
     */
    public function bulkMarkMoviesAsWatched(User $user, array $tmdbMovieIds): int
    {
        return DB::transaction(function () use ($user, $tmdbMovieIds) {
            $watchedList = $user->movieLists()
                ->where('type', MovieList::TYPE_WATCHED)
                ->first();

            $watchlist = $user->movieLists()
                ->where('type', MovieList::TYPE_WATCHLIST)
                ->first();

            if (!$watchedList) {
                throw new Exception('Lista de filmes assistidos não encontrada');
            }

            $addedCount = 0;

            foreach ($tmdbMovieIds as $tmdbMovieId) {
                if (!$this->isMovieInList($watchedList, $tmdbMovieId)) {
                    if ($watchlist && $this->isMovieInList($watchlist, $tmdbMovieId)) {
                        $this->removeMovieFromList($watchlist, $tmdbMovieId);
                    }

                    $metadata = [
                        'watched_at' => now(),
                    ];

                    $this->addMovieToList($watchedList, $tmdbMovieId, $metadata);
                    $addedCount++;
                }
            }

            return $addedCount;
        });
    }
}
