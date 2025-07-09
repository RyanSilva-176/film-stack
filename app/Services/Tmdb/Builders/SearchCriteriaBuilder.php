<?php

namespace App\Services\Tmdb\Builders;

class SearchCriteriaBuilder
{
    private array $criteria = [];

    public static function create(): self
    {
        return new self();
    }

    /**
     ** Define a query de busca
     */
    public function withQuery(string $query): self
    {
        $this->criteria['query'] = trim($query);
        return $this;
    }

    /**
     ** Define a página para paginação
     */
    public function withPage(int $page): self
    {
        $this->criteria['page'] = max(1, $page);
        return $this;
    }

    /**
     ** Filtra por gênero específico
     */
    public function withGenre(int $genreId): self
    {
        $this->criteria['with_genres'] = $genreId;
        return $this;
    }

    /**
     ** Filtra por ano específico
     */
    public function withYear(int $year): self
    {
        $this->criteria['year'] = $year;
        return $this;
    }

    /**
     ** Filtra por avaliação mínima
     */
    public function withRating(float $minRating, int $minVotes = 100): self
    {
        $this->criteria['vote_average.gte'] = $minRating;
        $this->criteria['vote_count.gte'] = $minVotes;
        return $this;
    }

    /**
     ** Filtra por intervalo de datas de lançamento
     */
    public function withDateRange(string $startDate, string $endDate): self
    {
        $this->criteria['release_date.gte'] = $startDate;
        $this->criteria['release_date.lte'] = $endDate;
        return $this;
    }

    /**
     ** Define ordenação
     */
    public function withSorting(string $sortBy): self
    {
        $this->criteria['sort_by'] = $sortBy;
        return $this;
    }

    /**
     ** Define se deve incluir conteúdo adulto
     */
    public function includeAdult(bool $include = false): self
    {
        $this->criteria['include_adult'] = $include;
        return $this;
    }

    /**
     ** Adiciona filtros customizados
     */
    public function withCustomFilters(array $filters): self
    {
        $this->criteria = array_merge($this->criteria, $filters);
        return $this;
    }

    /**
     ** Filtra por múltiplos gêneros
     */
    public function withGenres(array $genreIds): self
    {
        $this->criteria['with_genres'] = implode(',', $genreIds);
        return $this;
    }

    /**
     ** Filtra por idioma original
     */
    public function withOriginalLanguage(string $language): self
    {
        $this->criteria['with_original_language'] = $language;
        return $this;
    }

    /**
     ** Filtra por duração mínima (em minutos)
     */
    public function withMinRuntime(int $minutes): self
    {
        $this->criteria['with_runtime.gte'] = $minutes;
        return $this;
    }

    /**
     ** Filtra por duração máxima (em minutos)
     */
    public function withMaxRuntime(int $minutes): self
    {
        $this->criteria['with_runtime.lte'] = $minutes;
        return $this;
    }

    /**
     ** Constrói e retorna o array de critérios
     */
    public function build(): array
    {
        return $this->criteria;
    }
}
