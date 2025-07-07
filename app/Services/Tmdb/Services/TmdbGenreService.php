<?php

namespace App\Services\Tmdb\Services;

use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\DTOs\GenreDTO;

class TmdbGenreService extends TmdbBaseService implements TmdbGenreServiceInterface
{
    public const GENRES_CACHE_KEY = 'tmdb_movie_genres';

    /**
     ** Busca lista de gêneros de filmes
     * @return array|null
     */
    public function getMovieGenres(): ?array
    {
        return $this->makeRequest('/genre/movie/list', [], true, $this->staticCacheTimeout);
    }

    /**
     ** Busca gênero específico por ID
     * @param int $genreId
     * @return GenreDTO|null
     */
    public function getGenreById(int $genreId): ?GenreDTO
    {
        $genres = $this->getMovieGenres();

        if (!$genres || !isset($genres['genres'])) {
            return null;
        }

        foreach ($genres['genres'] as $genre) {
            if ($genre['id'] === $genreId) {
                return GenreDTO::fromArray($genre);
            }
        }

        return null;
    }

    /**
     ** Mapeia IDs de gêneros para nomes
     * @param array $genreIds
     * @return array
     */
    public function mapGenreIdsToNames(array $genreIds): array
    {
        $genres = $this->getMovieGenres();

        if (!$genres || !isset($genres['genres'])) {
            return [];
        }

        $genreMap = [];
        foreach ($genres['genres'] as $genre) {
            $genreMap[$genre['id']] = $genre['name'];
        }

        return array_map(fn($id) => $genreMap[$id] ?? 'Desconhecido', $genreIds);
    }

    /**
     ** Enriquece filme com informações de gêneros
     * @param array $movie
     * @return array
     */
    public function enrichMovieWithGenres(array $movie): array
    {
        if (isset($movie['genre_ids']) && (!isset($movie['genres']) || empty($movie['genres']))) {
            $genreNames = $this->mapGenreIdsToNames($movie['genre_ids']);
            $movie['genre_names'] = $genreNames;

            $allGenres = $this->getMovieGenres();
            if (isset($allGenres['genres'])) {
                $movie['genres'] = array_filter($allGenres['genres'], function($genre) use ($movie) {
                    return in_array($genre['id'], $movie['genre_ids']);
                });
            }
        } elseif (isset($movie['genres']) && !empty($movie['genres'])) {
            $movie['genre_names'] = array_column($movie['genres'], 'name');
            
            if (!isset($movie['genre_ids']) || empty($movie['genre_ids'])) {
                $movie['genre_ids'] = array_column($movie['genres'], 'id');
            }
        } else {
            $movie['genre_names'] = [];
            $movie['genre_ids'] = [];
            $movie['genres'] = [];
        }

        return $movie;
    }

    /**
     ** Retorna todos os gêneros como DTOs
     * @return array<GenreDTO>
     */
    public function getAllGenresAsDTO(): array
    {
        $genres = $this->getMovieGenres();

        if (!$genres || !isset($genres['genres'])) {
            return [];
        }

        return array_map(
            fn($genre) => GenreDTO::fromArray($genre),
            $genres['genres']
        );
    }

    /**
     ** Busca gêneros por IDs e retorna como DTOs
     * @param array $genreIds
     * @return array<GenreDTO>
     */
    public function getGenresByIds(array $genreIds): array
    {
        $allGenres = $this->getAllGenresAsDTO();

        return array_filter(
            $allGenres,
            fn(GenreDTO $genre) => in_array($genre->id, $genreIds)
        );
    }

    /**
     ** Busca gêneros por nome (busca parcial)
     * @param string $searchTerm
     * @return array<GenreDTO>
     */
    public function searchGenresByName(string $searchTerm): array
    {
        $allGenres = $this->getAllGenresAsDTO();
        $searchTerm = strtolower(trim($searchTerm));

        return array_filter(
            $allGenres,
            fn(GenreDTO $genre) => str_contains(strtolower($genre->name), $searchTerm)
        );
    }
}
