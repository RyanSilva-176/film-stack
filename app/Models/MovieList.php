<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MovieList extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'name',
        'type',
        'description',
        'is_public',
        'sort_order',
    ];

    protected $casts = [
        'is_public' => 'boolean',
    ];

    public const TYPE_LIKED = 'liked';
    public const TYPE_WATCHLIST = 'watchlist';
    public const TYPE_WATCHED = 'watched';
    public const TYPE_CUSTOM = 'custom';

    public const DEFAULT_TYPES = [
        self::TYPE_LIKED => 'Filmes Curtidos',
        self::TYPE_WATCHLIST => 'Quero Assistir',
        self::TYPE_WATCHED => 'Assistidos',
    ];

    /**
     ** Relacionamento com o usuário
     *? BeloongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     ** Relacionamento com os itens da lista
     *? HasMany
     */
    public function items(): HasMany
    {
        return $this->hasMany(MovieListItem::class)->orderBy('sort_order');
    }

    /**
     ** Verifica se é uma lista padrão
     */
    public function isDefaultType(): bool
    {
        return array_key_exists($this->type, self::DEFAULT_TYPES);
    }

    /**
     ** Scope para listas públicas
     */
    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    /**
     ** Scope para listas do usuário
     */
    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    /**
     ** Scope para tipo de lista
     */
    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }
}
