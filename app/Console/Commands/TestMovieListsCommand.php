<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Movie\Contracts\MovieListServiceInterface;
use Illuminate\Console\Command;

class TestMovieListsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:movie-lists';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Testa as funcionalidades das listas de filmes';

    public function __construct(
        protected MovieListServiceInterface $movieListService
    ) {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Testando funcionalidades das listas de filmes...');
        $this->newLine();

        $user = User::find(2);
        if (!$user) {
            $this->error('Nenhum usuário encontrado! Execute o seeder primeiro.');
            return;
        }

        $this->info("Usuário: {$user->name} ({$user->email})");
        $this->newLine();

        $lists = $this->movieListService->getUserLists($user);
        
        $this->info("Listas do usuário ({$lists->count()}):");
        foreach ($lists as $list) {
            $this->line("  • {$list->name} ({$list->type}) - {$list->items->count()} filmes");
        }
        $this->newLine();

        $watchlist = $user->getWatchlistMovies();
        if ($watchlist) {
            $this->info('Testando adicionar filme à watchlist...');
            
            $newMovieId = 278;
            $item = $this->movieListService->addMovieToList($watchlist, $newMovieId);
            
            $this->info("Filme {$newMovieId} adicionado à watchlist!");
        }

        $this->info('Testando curtir filme...');
        $liked = $this->movieListService->toggleMovieLike($user, 278);
        $this->info($liked ? 'Filme curtido!' : 'Filme descurtido!');

        $this->info('Testando marcar filme como assistido...');
        $this->movieListService->markMovieAsWatched($user, 278, 9, 'Filme incrível!');
        $this->info('Filme marcado como assistido com nota 9!');

        $likedList = $user->getLikedMoviesList();
        if ($likedList) {
            $this->info('Buscando filmes curtidos com detalhes do TMDB...');
            $moviesData = $this->movieListService->getListMoviesWithDetails($likedList);
            
            $this->info("{$moviesData['pagination']['total_items']} filmes encontrados:");
            foreach ($moviesData['movies'] as $movie) {
                $this->line("  • {$movie['title']} ({$movie['release_date']})");
                if ($movie['user_metadata']['rating']) {
                    $this->line("    Nota: {$movie['user_metadata']['rating']}/10");
                }
            }
        }

        $this->newLine();
        $this->info('Testes concluídos com sucesso!');
    }
}
