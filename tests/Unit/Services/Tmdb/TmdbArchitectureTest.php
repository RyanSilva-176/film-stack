<?php

namespace Tests\Unit\Services\Tmdb;

use Tests\TestCase;
use App\Services\Tmdb\DTOs\MovieDTO;
use App\Services\Tmdb\DTOs\GenreDTO;

class TmdbArchitectureTest extends TestCase
{
    public function testMovieDTOCreation(): void
    {
        $movieData = [
            'id' => 123,
            'title' => 'Teste Movie',
            'original_title' => 'Test Movie',
            'overview' => 'A test movie overview',
            'poster_path' => '/test-poster.jpg',
            'backdrop_path' => '/test-backdrop.jpg',
            'release_date' => '2023-01-01',
            'vote_average' => 8.5,
            'vote_count' => 1000,
            'adult' => false,
            'original_language' => 'en',
            'popularity' => 100.0,
            'genre_ids' => [28, 12],
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 12, 'name' => 'Adventure']
            ]
        ];

        $movieDTO = MovieDTO::fromArray($movieData);

        $this->assertEquals(123, $movieDTO->id);
        $this->assertEquals('Teste Movie', $movieDTO->title);
        $this->assertEquals(8.5, $movieDTO->voteAverage);
        $this->assertEquals([28, 12], $movieDTO->genreIds);
        $this->assertCount(2, $movieDTO->genres);
        $this->assertInstanceOf(GenreDTO::class, $movieDTO->genres[0]);
    }

    public function testMovieDTOHelperMethods(): void
    {
        $movieData = [
            'id' => 123,
            'title' => 'Test Movie',
            'original_title' => 'Test Movie',
            'overview' => 'Overview',
            'poster_path' => null,
            'backdrop_path' => null,
            'release_date' => '2020-01-01',
            'vote_average' => 8.5,
            'vote_count' => 1000,
            'adult' => false,
            'original_language' => 'en',
            'popularity' => 100.0,
            'genre_ids' => [28, 12],
            'genres' => [
                ['id' => 28, 'name' => 'Action'],
                ['id' => 12, 'name' => 'Adventure']
            ]
        ];

        $movieDTO = MovieDTO::fromArray($movieData);

        $this->assertTrue($movieDTO->isReleased());
        $this->assertTrue($movieDTO->hasHighRating());
        $this->assertTrue($movieDTO->hasGenre(28));
        $this->assertFalse($movieDTO->hasGenre(99));
        $this->assertEquals(['Action', 'Adventure'], $movieDTO->getGenreNames());
        $this->assertEquals(2020, $movieDTO->getYearOfRelease());
    }

    public function testGenreDTOCreation(): void
    {
        $genreData = ['id' => 28, 'name' => 'Action'];

        $genreDTO = GenreDTO::fromArray($genreData);

        $this->assertEquals(28, $genreDTO->id);
        $this->assertEquals('Action', $genreDTO->name);
    }

    public function testMovieDTOSerialization(): void
    {
        $movieData = [
            'id' => 123,
            'title' => 'Test Movie',
            'original_title' => 'Test Movie',
            'overview' => 'Overview',
            'poster_path' => '/poster.jpg',
            'backdrop_path' => '/backdrop.jpg',
            'release_date' => '2023-01-01',
            'vote_average' => 7.5,
            'vote_count' => 500,
            'adult' => false,
            'original_language' => 'en',
            'popularity' => 50.0,
            'genre_ids' => [28],
            'genres' => [['id' => 28, 'name' => 'Action']]
        ];

        $movieDTO = MovieDTO::fromArray($movieData);

        $serialized = $movieDTO->toArray();

        $this->assertIsArray($serialized);
        $this->assertEquals(123, $serialized['id']);
        $this->assertEquals('Test Movie', $serialized['title']);
        $this->assertEquals('2023-01-01', $serialized['release_date']);

        $json = json_encode($movieDTO);
        $this->assertIsString($json);
        $this->assertStringContainsString('Test Movie', $json);
    }

    public function testArchitectureServiceBinding(): void
    {
        $this->assertTrue(
            $this->app->bound(\App\Services\Tmdb\Contracts\TmdbMovieServiceInterface::class)
        );

        $this->assertTrue(
            $this->app->bound(\App\Services\Tmdb\Contracts\TmdbGenreServiceInterface::class)
        );

        $this->assertTrue(
            $this->app->bound(\App\Services\Tmdb\Contracts\TmdbSearchServiceInterface::class)
        );

        $this->assertTrue(
            $this->app->bound(\App\Services\Tmdb\Contracts\TmdbImageServiceInterface::class)
        );
    }

    public function testServiceResolution(): void
    {
        // Testa se os serviços podem ser resolvidos pelo container
        $movieService = $this->app->make(\App\Services\Tmdb\Contracts\TmdbMovieServiceInterface::class);
        $this->assertInstanceOf(
            \App\Services\Tmdb\Contracts\TmdbMovieServiceInterface::class,
            $movieService
        );

        $tmdbService = $this->app->make(\App\Services\Tmdb\TmdbService::class);
        $this->assertInstanceOf(\App\Services\Tmdb\TmdbService::class, $tmdbService);
    }
}