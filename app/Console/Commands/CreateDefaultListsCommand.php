<?php

namespace App\Console\Commands;

use App\Models\User;
use App\Services\Movie\Contracts\MovieListServiceInterface;
use Illuminate\Console\Command;
use Exception;

class CreateDefaultListsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'movie-lists:create-default {--user-id= : ID do usuário específico}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Cria listas padrão para usuários que não as possuem';

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
        $userId = $this->option('user-id');
        
        if ($userId) {
            $users = User::where('id', $userId)->get();
            
            if ($users->isEmpty()) {
                $this->error("Usuário com ID {$userId} não encontrado!");
                return;
            }
        } else {
            $users = User::all();
        }

        $this->info('Criando listas padrão para usuários...');
        $this->newLine();

        foreach ($users as $user) {
            $this->info("Processando usuário: {$user->name} ({$user->email})");
            
            try {
                $this->movieListService->createDefaultListsForUser($user);
                $this->info("Listas padrão criadas/verificadas para {$user->name}");
            } catch (Exception $e) {
                $this->error("Erro ao criar listas para {$user->name}: {$e->getMessage()}");
            }
        }

        $this->newLine();
        $this->info('Processo concluído!');
    }
}
