<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovieListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'movie_list_id',
        'tmdb_movie_id',
        'watched_at',
        'rating',
        'notes',
        'sort_order',
    ];

    protected $casts = [
        'watched_at' => 'datetime',
        'rating' => 'integer',
    ];

    /**
     ** Relacionamento com a lista de filmes
     *? BelongsTo
     */
    public function movieList(): BelongsTo
    {
        return $this->belongsTo(MovieList::class);
    }

    /**
     ** Scope para filmes específicos
     */
    public function scopeForMovie($query, int $tmdbMovieId)
    {
        return $query->where('tmdb_movie_id', $tmdbMovieId);
    }

    /**
     ** Scope para filmes com rating
     */
    public function scopeWithRating($query)
    {
        return $query->whereNotNull('rating');
    }

    /**
     ** Scope para filmes assistidos
     */
    public function scopeWatched($query)
    {
        return $query->whereNotNull('watched_at');
    }
}
