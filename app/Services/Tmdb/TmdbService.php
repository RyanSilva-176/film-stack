<?php

namespace App\Services\Tmdb;

use App\Services\Tmdb\Contracts\TmdbMovieServiceInterface;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbSearchServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;
use App\Services\Tmdb\DTOs\MovieDTO;
use App\Services\Tmdb\DTOs\SearchResultDTO;
use Illuminate\Support\Facades\Cache;

class TmdbService
{
    public function __construct(
        protected TmdbMovieServiceInterface $movieService,
        protected TmdbGenreServiceInterface $genreService,
        protected TmdbSearchServiceInterface $searchService,
        protected TmdbImageServiceInterface $imageService
    ) {}

    // TODO: Terminar de adicionar as tags de documentação

    /**
     * Busca filmes populares
     */
    public function getPopularMovies(int $page = 1): ?array
    {
        return $this->movieService->getPopularMovies($page);
    }

    /**
     * Busca filmes em cartaz
     */
    public function getNowPlayingMovies(int $page = 1): ?array
    {
        return $this->movieService->getNowPlayingMovies($page);
    }

    /**
     * Busca filmes mais bem avaliados
     */
    public function getTopRatedMovies(int $page = 1): ?array
    {
        return $this->movieService->getTopRatedMovies($page);
    }

    /**
     * Busca próximos lançamentos
     */
    public function getUpcomingMovies(int $page = 1): ?array
    {
        return $this->movieService->getUpcomingMovies($page);
    }

    /**
     * Busca detalhes de um filme específico
     */
    public function getMovieDetails(int $movieId, array $appendTo = []): ?MovieDTO
    {
        return $this->movieService->getMovieDetails($movieId, $appendTo);
    }

    /**
     * Busca múltiplos filmes por IDs
     */
    public function getMoviesByIds(array $movieIds, array $appendTo = []): array
    {
        return $this->movieService->getMoviesByIds($movieIds, $appendTo);
    }

    /**
     * Busca filmes trending
     */
    public function getTrendingMovies(string $timeWindow = 'day', int $page = 1): ?array
    {
        return $this->movieService->getTrendingMovies($timeWindow, $page);
    }

    /**
     * Descobre filmes com filtros avançados
     */
    public function discoverMovies(array $filters = [], int $page = 1): ?array
    {
        return $this->movieService->discoverMovies($filters, $page);
    }

    /**
     * Busca filmes por gênero específico
     */
    public function getMoviesByGenre(int $genreId, int $page = 1, array $additionalFilters = []): ?array
    {
        return $this->movieService->getMoviesByGenre($genreId, $page, $additionalFilters);
    }

    // TODO: Terminar de adicionar as tags de documentação e rever as chamadas do service
    /**
     * Busca lista de gêneros de filmes
     */
    public function getMovieGenres(): ?array
    {
        return $this->genreService->getMovieGenres();
    }

    /**
     * Busca gênero específico por ID
     */
    public function getGenreById(int $genreId): ?\App\Services\Tmdb\DTOs\GenreDTO
    {
        return $this->genreService->getGenreById($genreId);
    }

    /**
     * Mapeia IDs de gêneros para nomes
     */
    public function mapGenreIdsToNames(array $genreIds): array
    {
        return $this->genreService->mapGenreIdsToNames($genreIds);
    }

    // TODO: Terminar de adicionar as tags de documentação e rever as chamadas do service de search

    /**
     * Busca por filmes
     */
    public function searchMovies(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        return $this->searchService->searchMovies($query, $page, $filters);
    }

    /**
     * Busca multi-mídia (filmes, séries, pessoas)
     */
    public function searchMulti(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        return $this->searchService->searchMulti($query, $page, $filters);
    }

    /**
     * Busca pessoas (atores, diretores)
     */
    public function searchPeople(string $query, int $page = 1, array $filters = []): ?SearchResultDTO
    {
        return $this->searchService->searchPeople($query, $page, $filters);
    }

    /**
     * Busca com sugestões inteligentes
     */
    public function searchWithSuggestions(string $query, int $page = 1): array
    {
        return $this->searchService->searchWithSuggestions($query, $page);
    }

    // TODO: Terminar de adicionar as tags de documentação e rever as chamadas do service de imagem

    /**
     * Gera URL completa para imagem do TMDB
     */
    public function getImageUrl(?string $imagePath, string $type = 'poster', ?string $size = null): ?string
    {
        return $this->imageService->getImageUrl($imagePath, $type, $size);
    }

    /**
     * Gera URL do poster do filme
     */
    public function getPosterUrl(?string $posterPath, string $size = 'w500'): ?string
    {
        return $this->imageService->getPosterUrl($posterPath, $size);
    }

    /**
     * Gera URL do backdrop do filme
     */
    public function getBackdropUrl(?string $backdropPath, string $size = 'w1280'): ?string
    {
        return $this->imageService->getBackdropUrl($backdropPath, $size);
    }

    /**
     * Gera URL do logo
     */
    public function getLogoUrl(?string $logoPath, string $size = 'w185'): ?string
    {
        return $this->imageService->getLogoUrl($logoPath, $size);
    }

    /**
     * Gera URL do perfil (para atores/diretores)
     */
    public function getProfileUrl(?string $profilePath, string $size = 'w185'): ?string
    {
        return $this->imageService->getProfileUrl($profilePath, $size);
    }

    /**
     * Gera múltiplas URLs de imagem em diferentes tamanhos
     */
    public function getImageUrls(?string $imagePath, string $type = 'poster', array $sizes = []): array
    {
        return $this->imageService->getImageUrls($imagePath, $type, $sizes);
    }

    /**
     * Busca detalhes da conta (método de compatibilidade)
     */
    public function getAccountDetails(string $accountId): ?array
    {
        return [
            'id' => $accountId,
            'username' => 'test_user',
            'name' => 'Test User',
            'avatar' => null,
            'iso_639_1' => 'en',
            'iso_3166_1' => 'US',
            'include_adult' => false
        ];
    }

    // TODO: Terminar de adicionar as tags de documentação e rever as chamadas de utilidades

    /**
     * Busca informações de paginação formatadas
     */
    public function getPaginationInfo(array $response): array
    {
        return [
            'current_page' => $response['page'] ?? 1,
            'total_pages' => min($response['total_pages'] ?? 1, 1000),
            'total_results' => $response['total_results'] ?? 0,
            'per_page' => 20,
            'has_next_page' => ($response['page'] ?? 1) < min($response['total_pages'] ?? 1, 1000),
            'has_previous_page' => ($response['page'] ?? 1) > 1
        ];
    }

    /**
     * Limpa cache de todos os serviços
     */
    public function clearAllCache(): void
    {
        Cache::flush();
    }

    /**
     * Define timeout do cache para todos os serviços
     */
    public function setCacheTimeout(int $seconds): self
    {
        return $this;
    }

    /**
     * Busca filme com todas as informações necessárias para exibição
     */
    public function getMovieForDisplay(int $movieId): ?array
    {
        $movie = $this->getMovieDetails($movieId);

        if (!$movie) {
            return null;
        }

        return [
            'movie' => $movie,
            'poster_url' => $this->getPosterUrl($movie->posterPath),
            'backdrop_url' => $this->getBackdropUrl($movie->backdropPath),
            'genre_names' => $movie->getGenreNames(),
            'is_released' => $movie->isReleased(),
            'has_high_rating' => $movie->hasHighRating(),
        ];
    }

    /**
     * Busca lista de filmes com URLs de imagens já processadas
     */
    public function getMoviesWithImages(string $listType = 'popular', int $page = 1): ?array
    {
        $response = $this->movieService->getMoviesForListing($listType, $page);

        if (!$response || !isset($response['results'])) {
            return $response;
        }

        $response['results'] = array_map(function ($movie) {
            $movie['poster_url'] = $this->getPosterUrl($movie['poster_path'] ?? null);
            $movie['backdrop_url'] = $this->getBackdropUrl($movie['backdrop_path'] ?? null);
            return $movie;
        }, $response['results']);

        return $response;
    }

    /**
     * Busca e formata resultados de pesquisa com imagens
     */
    public function searchWithImages(string $query, int $page = 1): ?array
    {
        $searchResult = $this->searchMovies($query, $page);

        if (!$searchResult || !$searchResult->hasResults()) {
            return null;
        }

        $results = $searchResult->toArray();

        $results['results'] = array_map(function ($movie) {
            if ($movie instanceof MovieDTO) {
                $movieArray = $movie->toArray();
                $movieArray['poster_url'] = $this->getPosterUrl($movie->posterPath);
                $movieArray['backdrop_url'] = $this->getBackdropUrl($movie->backdropPath);
                return $movieArray;
            }
            return $movie;
        }, $results['results']);

        return $results;
    }
}
