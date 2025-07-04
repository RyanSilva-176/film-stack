<?php

namespace App\Services\Movie;

use App\Models\MovieList;
use App\Models\MovieListItem;
use App\Models\User;
use App\Services\Movie\Contracts\MovieListServiceInterface;
use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Exception;

class MovieListService implements MovieListServiceInterface
{
    public function __construct(
        protected TmdbMovieServiceInterface $tmdbMovieService
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
        // Verifica se o filme já está na lista
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

        // Adiciona metadados se fornecidos
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
    public function getListMoviesWithDetails(MovieList $list, int $page = 1): array
    {
        $perPage = 20;
        $offset = ($page - 1) * $perPage;

        $items = $list->items()
            ->orderBy('sort_order')
            ->skip($offset)
            ->take($perPage)
            ->get();

        $movieIds = $items->pluck('tmdb_movie_id')->toArray();
        
        if (empty($movieIds)) {
            return [
                'movies' => [],
                'pagination' => [
                    'current_page' => $page,
                    'total_pages' => 0,
                    'total_items' => 0,
                    'per_page' => $perPage,
                ]
            ];
        }

        // Busca detalhes dos filmes no TMDB
        $tmdbMovies = $this->tmdbMovieService->getMoviesByIds($movieIds);

        // Combina dados do TMDB com metadados locais
        $movies = [];
        foreach ($items as $item) {
            $tmdbData = collect($tmdbMovies)->firstWhere('id', $item->tmdb_movie_id);
            
            if ($tmdbData) {
                $movieData = $tmdbData->toArray();
                $movieData['user_metadata'] = [
                    'rating' => $item->rating,
                    'notes' => $item->notes,
                    'watched_at' => $item->watched_at,
                    'added_at' => $item->created_at,
                ];
                $movies[] = $movieData;
            }
        }

        $totalItems = $list->items()->count();
        $totalPages = ceil($totalItems / $perPage);

        return [
            'movies' => $movies,
            'pagination' => [
                'current_page' => $page,
                'total_pages' => $totalPages,
                'total_items' => $totalItems,
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
            $watchlist = $user->getWatchlistMovies();
            $watchedList = $user->getWatchedMovies();

            if (!$watchedList) {
                throw new Exception('Lista de filmes assistidos não encontrada');
            }

            // Remove da watchlist se estiver lá
            if ($watchlist && $this->isMovieInList($watchlist, $tmdbMovieId)) {
                $this->removeMovieFromList($watchlist, $tmdbMovieId);
            }

            // Adiciona à lista de assistidos
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
            return false; // Descurtiu
        } else {
            $this->addMovieToList($likedList, $tmdbMovieId);
            return true; // Curtiu
        }
    }
}
