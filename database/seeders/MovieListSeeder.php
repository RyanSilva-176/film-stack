<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\MovieList;
use App\Models\MovieListItem;
use Illuminate\Database\Seeder;

class MovieListSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user = User::first();

        if (!$user) {
            $user = User::create([
                'name' => 'Usuário Teste',
                'email' => 'teste@example.com',
                'password' => bcrypt('password'),
                'email_verified_at' => now(),
            ]);
        }


        $likedList = $user->getLikedMoviesList();
        $watchlist = $user->getWatchlistMovies();
        $watchedList = $user->getWatchedMovies();

        $exampleMovies = [
            550, // Fight Club
            680, // Pulp Fiction
            13, // Forrest Gump
            429, // The Good, the Bad and the Ugly
            155, // The Dark Knight
            27205, // Inception
        ];

        if ($likedList) {
            foreach (array_slice($exampleMovies, 0, 3) as $movieId) {
                MovieListItem::firstOrCreate([
                    'movie_list_id' => $likedList->id,
                    'tmdb_movie_id' => $movieId,
                ]);
            }
        }

        if ($watchlist) {
            foreach (array_slice($exampleMovies, 2, 2) as $movieId) {
                MovieListItem::firstOrCreate([
                    'movie_list_id' => $watchlist->id,
                    'tmdb_movie_id' => $movieId,
                ]);
            }
        }

        if ($watchedList) {
            foreach (array_slice($exampleMovies, 0, 2) as $index => $movieId) {
                MovieListItem::firstOrCreate([
                    'movie_list_id' => $watchedList->id,
                    'tmdb_movie_id' => $movieId,
                ], [
                    'watched_at' => now()->subDays($index + 1),
                    'rating' => rand(7, 10),
                    'notes' => 'Filme excelente! Recomendo muito.',
                ]);
            }
        }

        $customList = MovieList::firstOrCreate([
            'user_id' => $user->id,
            'name' => 'Meus Filmes Favoritos de Ação',
            'type' => MovieList::TYPE_CUSTOM,
        ], [
            'description' => 'Uma lista com os melhores filmes de ação que já assisti',
            'is_public' => true,
            'sort_order' => 100,
        ]);

        foreach (array_slice($exampleMovies, 3, 3) as $movieId) {
            MovieListItem::firstOrCreate([
                'movie_list_id' => $customList->id,
                'tmdb_movie_id' => $movieId,
            ]);
        }
    }
}
