<?php

namespace Tests\Unit\Services\Tmdb\Refactored;

use Tests\TestCase;
use App\Services\Tmdb\Enrichers\MovieDataEnricher;
use App\Services\Tmdb\Contracts\TmdbGenreServiceInterface;
use App\Services\Tmdb\Contracts\TmdbImageServiceInterface;

class MovieDataEnricherTest extends TestCase
{
    private MovieDataEnricher $enricher;
    private $genreService;
    private $imageService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->genreService = $this->createMock(TmdbGenreServiceInterface::class);
        $this->imageService = $this->createMock(TmdbImageServiceInterface::class);

        $this->enricher = new MovieDataEnricher(
            $this->genreService,
            $this->imageService
        );
    }

    public function test_enriches_single_movie_with_all_data(): void
    {
        $movie = [
            'id' => 123,
            'title' => 'Test Movie',
            'poster_path' => '/test.jpg',
            'backdrop_path' => '/backdrop.jpg',
        ];

        $this->genreService
            ->expects($this->once())
            ->method('enrichMovieWithGenres')
            ->with($movie)
            ->willReturn(array_merge($movie, ['genre_names' => ['Action']]));

        $this->imageService
            ->expects($this->once())
            ->method('getPosterUrl')
            ->with('/test.jpg')
            ->willReturn('https://image.tmdb.org/poster.jpg');

        $this->imageService
            ->expects($this->once())
            ->method('getBackdropUrl')
            ->with('/backdrop.jpg')
            ->willReturn('https://image.tmdb.org/backdrop.jpg');

        $result = $this->enricher->enrichMovie($movie);

        $this->assertArrayHasKey('genre_names', $result);
        $this->assertArrayHasKey('poster_url', $result);
        $this->assertArrayHasKey('backdrop_url', $result);
        $this->assertEquals('', $result['overview']);
        $this->assertEquals(0.0, $result['vote_average']);
    }

    public function test_enriches_movie_collection(): void
    {
        $movies = [
            ['id' => 1, 'title' => 'Movie 1'],
            ['id' => 2, 'title' => 'Movie 2'],
        ];

        $this->genreService
            ->expects($this->exactly(2))
            ->method('enrichMovieWithGenres')
            ->willReturnCallback(fn($movie) => $movie);

        $this->imageService
            ->expects($this->exactly(2))
            ->method('getPosterUrl')
            ->willReturn('https://image.tmdb.org/poster.jpg');

        $this->imageService
            ->expects($this->exactly(2))
            ->method('getBackdropUrl')
            ->willReturn('https://image.tmdb.org/backdrop.jpg');

        $result = $this->enricher->enrichMovies($movies);

        $this->assertCount(2, $result);
        $this->assertArrayHasKey('poster_url', $result[0]);
        $this->assertArrayHasKey('backdrop_url', $result[1]);
    }

    public function test_normalizes_fields_with_correct_types(): void
    {
        $movie = [
            'id' => 123,
            'title' => 'Test Movie',
            'vote_average' => '7.5',
            'vote_count' => '100',
            'adult' => 1,
        ];

        $this->genreService->method('enrichMovieWithGenres')->willReturn($movie);
        $this->imageService->method('getPosterUrl')->willReturn(null);
        $this->imageService->method('getBackdropUrl')->willReturn(null);

        $result = $this->enricher->enrichMovie($movie);

        $this->assertIsFloat($result['vote_average']);
        $this->assertIsInt($result['vote_count']);
        $this->assertIsBool($result['adult']);
        $this->assertEquals(7.5, $result['vote_average']);
        $this->assertEquals(100, $result['vote_count']);
        $this->assertTrue($result['adult']);
    }
}
