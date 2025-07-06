<?php

namespace App\Services\Tmdb\DTOs;

use JsonSerializable;
use Carbon\Carbon;
use Exception;
use Illuminate\Support\Facades\Log;

class MovieDTO implements JsonSerializable
{
    public function __construct(
        public readonly int $id,
        public readonly string $title,
        public readonly ?string $originalTitle,
        public readonly ?string $overview,
        public readonly ?string $posterPath,
        public readonly ?string $backdropPath,
        public readonly ?string $posterUrl,
        public readonly ?string $backdropUrl,
        public readonly ?Carbon $releaseDate,
        public readonly float $voteAverage,
        public readonly int $voteCount,
        public readonly bool $adult,
        public readonly string $originalLanguage,
        public readonly float $popularity,
        public readonly array $genreIds,
        public readonly array $genres = [],
        public readonly ?string $homepage = null,
        public readonly ?int $runtime = null,
        public readonly ?string $status = null,
        public readonly ?string $tagline = null,
        public readonly ?int $budget = null,
        public readonly ?int $revenue = null,
        public readonly array $productionCompanies = [],
        public readonly array $productionCountries = [],
        public readonly array $spokenLanguages = [],
        public readonly ?array $credits = null,
        public readonly ?array $videos = null,
        public readonly ?array $images = null,
        public readonly ?array $recommendations = null,
        public readonly ?array $similar = null,
    ) {}

    public static function fromArray(array $data): self
    {
        $releaseDate = null;
        if (!empty($data['release_date'])) {
            try {
                $releaseDate = Carbon::createFromFormat('Y-m-d', $data['release_date']);
            } catch (Exception $e) {
                $releaseDate = null;

                // Todo: Ajustar esse retorno aqui
                Log::error('Invalid release date format', [
                    'data' => $data,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return new self(
            id: $data['id'],
            title: $data['title'],
            originalTitle: $data['original_title'] ?? null,
            overview: $data['overview'] ?? null,
            posterPath: $data['poster_path'] ?? null,
            backdropPath: $data['backdrop_path'] ?? null,
            posterUrl: $data['poster_url'] ?? null,
            backdropUrl: $data['backdrop_url'] ?? null,
            releaseDate: $releaseDate,
            voteAverage: (float) ($data['vote_average'] ?? 0),
            voteCount: (int) ($data['vote_count'] ?? 0),
            adult: (bool) ($data['adult'] ?? false),
            originalLanguage: $data['original_language'] ?? 'en',
            popularity: (float) ($data['popularity'] ?? 0),
            genreIds: $data['genre_ids'] ?? [],
            genres: array_map(
                fn($genre) => GenreDTO::fromArray($genre),
                $data['genres'] ?? []
            ),
            homepage: $data['homepage'] ?? null,
            runtime: $data['runtime'] ?? null,
            status: $data['status'] ?? null,
            tagline: $data['tagline'] ?? null,
            budget: $data['budget'] ?? null,
            revenue: $data['revenue'] ?? null,
            productionCompanies: $data['production_companies'] ?? [],
            productionCountries: $data['production_countries'] ?? [],
            spokenLanguages: $data['spoken_languages'] ?? [],
            credits: $data['credits'] ?? null,
            videos: $data['videos'] ?? null,
            images: $data['images'] ?? null,
            recommendations: $data['recommendations'] ?? null,
            similar: $data['similar'] ?? null,
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'original_title' => $this->originalTitle,
            'overview' => $this->overview,
            'poster_path' => $this->posterPath,
            'backdrop_path' => $this->backdropPath,
            'poster_url' => $this->posterUrl,
            'backdrop_url' => $this->backdropUrl,
            'release_date' => $this->releaseDate?->format('Y-m-d'),
            'vote_average' => $this->voteAverage,
            'vote_count' => $this->voteCount,
            'adult' => $this->adult,
            'original_language' => $this->originalLanguage,
            'popularity' => $this->popularity,
            'genre_ids' => $this->genreIds,
            'genres' => array_map(fn(GenreDTO $genre) => $genre->toArray(), $this->genres),
            'homepage' => $this->homepage,
            'runtime' => $this->runtime,
            'status' => $this->status,
            'tagline' => $this->tagline,
            'budget' => $this->budget,
            'revenue' => $this->revenue,
            'production_companies' => $this->productionCompanies,
            'production_countries' => $this->productionCountries,
            'spoken_languages' => $this->spokenLanguages,
            'credits' => $this->credits,
            'videos' => $this->videos,
            'images' => $this->images,
            'recommendations' => $this->recommendations,
            'similar' => $this->similar,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    public function hasGenre(int $genreId): bool
    {
        return in_array($genreId, $this->genreIds);
    }

    public function getGenreNames(): array
    {
        return array_map(fn(GenreDTO $genre) => $genre->name, $this->genres);
    }

    public function isReleased(): bool
    {
        return $this->releaseDate && $this->releaseDate->isPast();
    }

    public function getFormattedReleaseDate(string $format = 'd/m/Y'): ?string
    {
        return $this->releaseDate?->format($format);
    }

    public function getYearOfRelease(): ?int
    {
        return $this->releaseDate?->year;
    }

    public function hasHighRating(float $threshold = 7.0): bool
    {
        return $this->voteAverage >= $threshold;
    }
}
