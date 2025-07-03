<?php

namespace App\Services\Tmdb\Contracts;

use App\Services\Tmdb\DTOs\GenreDTO;

interface TmdbGenreServiceInterface
{
    /**
     * Busca lista de gêneros de filmes
     */
    public function getMovieGenres(): ?array;

    /**
     * Busca gênero específico por ID
     */
    public function getGenreById(int $genreId): ?GenreDTO;

    /**
     * Mapeia IDs de gêneros para nomes
     */
    public function mapGenreIdsToNames(array $genreIds): array;

    /**
     * Enriquece filme com informações de gêneros
     */
    public function enrichMovieWithGenres(array $movie): array;

    /**
     * Busca gêneros por nome (busca parcial)
     */
    public function searchGenresByName(string $searchTerm): array;
}
