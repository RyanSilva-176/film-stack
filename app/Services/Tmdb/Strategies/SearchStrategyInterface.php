<?php

namespace App\Services\Tmdb\Strategies;

use App\Services\Tmdb\DTOs\SearchResultDTO;

interface SearchStrategyInterface
{
    public function search(string $query, int $page, array $filters): ?SearchResultDTO;
}
