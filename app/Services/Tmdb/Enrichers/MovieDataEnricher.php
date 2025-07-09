<?php

namespace App\Services\Tmdb\Enrichers;

use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;


class MovieDataEnricher
{
    public function __construct(
        private TmdbGenreServiceInterface $genreService,
        private TmdbImageServiceInterface $imageService
    ) {}

    /**
     ** Enriquece um único filme com todas as informações necessárias
     */
    public function enrichMovie(array $movie): array
    {
        $movie = $this->genreService->enrichMovieWithGenres($movie);
        $movie = $this->addImageUrls($movie);
        return $this->normalizeFields($movie);
    }

    /**
     ** Enriquece uma coleção de filmes
     */
    public function enrichMovies(array $movies): array
    {
        return array_map([$this, 'enrichMovie'], $movies);
    }

    /**
     ** Adiciona URLs das imagens ao filme
     */
    private function addImageUrls(array $movie): array
    {
        $movie['poster_url'] = $this->imageService->getPosterUrl($movie['poster_path'] ?? null);
        $movie['backdrop_url'] = $this->imageService->getBackdropUrl($movie['backdrop_path'] ?? null);
        return $movie;
    }

    /**
     ** Normaliza campos garantindo tipos corretos e valores padrão
     */
    private function normalizeFields(array $movie): array
    {
        return array_merge($movie, [
            'overview' => $movie['overview'] ?? '',
            'release_date' => $movie['release_date'] ?? null,
            'vote_average' => (float) ($movie['vote_average'] ?? 0),
            'vote_count' => (int) ($movie['vote_count'] ?? 0),
            'adult' => (bool) ($movie['adult'] ?? false),
            'video' => (bool) ($movie['video'] ?? false),
            'popularity' => (float) ($movie['popularity'] ?? 0),
        ]);
    }
}
