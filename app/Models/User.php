<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\HasMany;
use InvalidArgumentException;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'avatar',
        'provider',
        'provider_id',
        'email_verified_at',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($user) {
            if ($user->provider && $user->provider_id) {
                return true;
            }

            if (empty($user->password)) {
                throw new InvalidArgumentException('Password é obrigatório para usuários não OAuth');
            }

            return true;
        });
    }

    /**
     * * Verifica se é usuário OAuth
     * @return bool
     */
    public function isOAuthUser(): bool
    {
        return !empty($this->provider) && !empty($this->provider_id);
    }

    /**
     ** Verifica se tem senha definida
     * @return bool
     */
    public function hasPassword(): bool
    {
        return !empty($this->password);
    }

    /**
     ** Relacionamento com as listas de filmes
     */
    public function movieLists(): HasMany
    {
        return $this->hasMany(MovieList::class)->orderBy('sort_order');
    }

    /**
     ** Busca lista específica por tipo
     */
    public function getListByType(string $type): ?MovieList
    {
        return $this->movieLists()->where('type', $type)->first();
    }

    /**
     ** Busca lista de filmes curtidos
     */
    public function getLikedMoviesList(): ?MovieList
    {
        return $this->getListByType(MovieList::TYPE_LIKED);
    }

    /**
     ** Busca lista de filmes para assistir
     */
    public function getWatchlistMovies(): ?MovieList
    {
        return $this->getListByType(MovieList::TYPE_WATCHLIST);
    }

    /**
     ** Busca lista de filmes assistidos
     */
    public function getWatchedMovies(): ?MovieList
    {
        return $this->getListByType(MovieList::TYPE_WATCHED);
    }
}
