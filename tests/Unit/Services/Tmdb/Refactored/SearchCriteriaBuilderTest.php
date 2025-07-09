<?php

namespace Tests\Unit\Services\Tmdb\Refactored;

use Tests\TestCase;
use App\Services\Tmdb\Builders\SearchCriteriaBuilder;

class SearchCriteriaBuilderTest extends TestCase
{
    public function test_creates_basic_search_criteria(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withQuery('Batman')
            ->withPage(2)
            ->build();

        $this->assertEquals([
            'query' => 'Batman',
            'page' => 2,
        ], $criteria);
    }

    public function test_creates_advanced_search_criteria(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withQuery('Action Movies')
            ->withGenre(28) // Action
            ->withYear(2023)
            ->withRating(7.0, 500)
            ->withSorting('vote_average.desc')
            ->includeAdult(false)
            ->build();

        $expected = [
            'query' => 'Action Movies',
            'with_genres' => 28,
            'year' => 2023,
            'vote_average.gte' => 7.0,
            'vote_count.gte' => 500,
            'sort_by' => 'vote_average.desc',
            'include_adult' => false,
        ];

        $this->assertEquals($expected, $criteria);
    }

    public function test_creates_date_range_criteria(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withDateRange('2020-01-01', '2020-12-31')
            ->build();

        $this->assertEquals([
            'release_date.gte' => '2020-01-01',
            'release_date.lte' => '2020-12-31',
        ], $criteria);
    }

    public function test_creates_runtime_filter_criteria(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withMinRuntime(90)
            ->withMaxRuntime(180)
            ->build();

        $this->assertEquals([
            'with_runtime.gte' => 90,
            'with_runtime.lte' => 180,
        ], $criteria);
    }

    public function test_handles_multiple_genres(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withGenres([28, 12, 16]) // Action, Adventure, Animation
            ->build();

        $this->assertEquals([
            'with_genres' => '28,12,16',
        ], $criteria);
    }

    public function test_adds_custom_filters(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withQuery('Movie')
            ->withCustomFilters([
                'with_companies' => '2',
                'without_genres' => '27', // Horror
            ])
            ->build();

        $expected = [
            'query' => 'Movie',
            'with_companies' => '2',
            'without_genres' => '27',
        ];

        $this->assertEquals($expected, $criteria);
    }

    public function test_fluent_interface_chaining(): void
    {
        $builder = SearchCriteriaBuilder::create();

        $result = $builder
            ->withQuery('Test')
            ->withPage(1)
            ->withGenre(28);

        $this->assertSame($builder, $result);
    }

    public function test_page_validation(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withPage(-1)
            ->build();

        $this->assertEquals(['page' => 1], $criteria);
    }

    public function test_trims_query_whitespace(): void
    {
        $criteria = SearchCriteriaBuilder::create()
            ->withQuery('  Batman  ')
            ->build();

        $this->assertEquals(['query' => 'Batman'], $criteria);
    }
}
