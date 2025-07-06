<?php

namespace App\Http\Controllers;

use App\Http\Requests\CreateMovieListRequest;
use App\Http\Requests\AddMovieToListRequest;
use App\Http\Requests\MarkMovieWatchedRequest;
use App\Models\MovieList;
use App\Services\Movie\Contracts\MovieListServiceInterface;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Exception;
use Inertia\Response;

class MovieListController extends Controller
{
    public function __construct(
        protected MovieListServiceInterface $movieListService
    ) {}

    /**
     ** Lista todas as listas do usuário autenticado
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $lists = $this->movieListService->getUserLists($user);

        return response()->json([
            'success' => true,
            'data' => $lists,
        ]);
    }

    /**
     ** Exibe uma lista específica com seus filmes
     * @param MovieList $movieList
     * @return JsonResponse
     */
    public function show(MovieList $movieList, Request $request): JsonResponse
    {
        if ($movieList->user_id !== Auth::id() && !$movieList->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Lista não encontrada ou não autorizada',
            ], 403);
        }

        $page = $request->get('page', 1);
        $filters = [
            'search' => $request->get('search'),
            'genre' => $request->get('genre'),
            'sort' => $request->get('sort', 'added_date_desc'),
            'per_page' => $request->get('per_page', 20),
        ];

        $moviesData = $this->movieListService->getListMoviesWithDetails($movieList, $page, $filters);

        return response()->json([
            'success' => true,
            'data' => [
                'list' => $movieList,
                'movies' => $moviesData['movies'],
                'pagination' => $moviesData['pagination'],
            ],
        ]);
    }

    /**
     ** Cria uma nova lista personalizada
     * @param CreateMovieListRequest $request
     * @return JsonResponse
     */
    public function store(CreateMovieListRequest $request): JsonResponse
    {
        $user = Auth::user();

        $list = $this->movieListService->createCustomList(
            $user,
            $request->input('name'),
            $request->input('description'),
            $request->boolean('is_public', false)
        );

        return response()->json([
            'success' => true,
            'message' => 'Lista criada com sucesso',
            'data' => $list,
        ], 201);
    }

    /**
     ** Adiciona filme a uma lista
     * @param MovieList $movieList
     * @param AddMovieToListRequest $request
     * @return JsonResponse
     */
    public function addMovie(MovieList $movieList, AddMovieToListRequest $request): JsonResponse
    {
        if ($movieList->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Lista não encontrada',
            ], 404);
        }

        $tmdbMovieId = $request->input('tmdb_movie_id');
        $metadata = $request->only(['rating', 'notes']);

        try {
            $item = $this->movieListService->addMovieToList($movieList, $tmdbMovieId, $metadata);

            return response()->json([
                'success' => true,
                'message' => 'Filme adicionado à lista com sucesso',
                'data' => $item,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao adicionar filme à lista',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     ** Remove filme de uma lista
     * @param MovieList $movieList
     * @param int $tmdbMovieId
     * @return JsonResponse
     */
    public function removeMovie(MovieList $movieList, int $tmdbMovieId): JsonResponse
    {
        if ($movieList->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Lista não encontrada',
            ], 404);
        }

        $removed = $this->movieListService->removeMovieFromList($movieList, $tmdbMovieId);

        if ($removed) {
            return response()->json([
                'success' => true,
                'message' => 'Filme removido da lista com sucesso',
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Filme não encontrado na lista',
        ], 404);
    }

    /**
     ** Marca filme como assistido
     * @param MarkMovieWatchedRequest $request
     * @return JsonResponse
     */
    public function markAsWatched(MarkMovieWatchedRequest $request): JsonResponse
    {
        $user = Auth::user();
        $tmdbMovieId = $request->input('tmdb_movie_id');
        $rating = $request->input('rating');
        $notes = $request->input('notes');

        try {
            $this->movieListService->markMovieAsWatched($user, $tmdbMovieId, $rating, $notes);

            return response()->json([
                'success' => true,
                'message' => 'Filme marcado como assistido',
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar filme como assistido',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     ** Curte/descurte um filme
     * @param Request $request
     * @return JsonResponse
     */
    public function toggleLike(Request $request): JsonResponse
    {
        $request->validate([
            'tmdb_movie_id' => 'required|integer|min:1',
        ]);

        $user = Auth::user();
        $tmdbMovieId = $request->input('tmdb_movie_id');

        try {
            $liked = $this->movieListService->toggleMovieLike($user, $tmdbMovieId);

            return response()->json([
                'success' => true,
                'message' => $liked ? 'Filme curtido' : 'Filme descurtido',
                'data' => ['liked' => $liked],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao curtir/descurtir filme',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     ** Deleta uma lista personalizada
     * @param MovieList $movieList
     * @return JsonResponse
     */
    public function destroy(MovieList $movieList): JsonResponse
    {
        if ($movieList->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Lista não encontrada',
            ], 404);
        }

        if ($movieList->isDefaultType()) {
            return response()->json([
                'success' => false,
                'message' => 'Não é possível deletar listas padrão',
            ], 400);
        }

        $movieList->delete();

        return response()->json([
            'success' => true,
            'message' => 'Lista deletada com sucesso',
        ]);
    }

    /**
     ** Retorna a página de listas de filmes
     * @return Response
     */
    public function likedPage(): Response
    {
        return Inertia::render('MovieLists/Liked');
    }

    /**
     ** Retorna a página de listas de filmes
     */
    public function watchlistPage(): Response
    {
        return Inertia::render('MovieLists/WatchList');
    }

    /**
     ** Retorna a página de listas de filmes
     */
    public function watchedPage(): Response
    {
        return Inertia::render('MovieLists/Watched');
    }

    /**
     ** Retorna a página de listas de filmes
     */
    public function customPage(): Response
    {
        return Inertia::render('MovieLists/Custom');
    }

    /**
     * Página de detalhes de uma lista personalizada
     */
    public function customListDetail(MovieList $movieList): Response
    {
        if ($movieList->user_id !== Auth::id()) {
            abort(404);
        }

        return Inertia::render('MovieLists/CustomListDetail', [
            'listId' => $movieList->id
        ]);
    }

    /**
     * Página pública de detalhes de uma lista personalizada
     */
    public function publicListDetail(MovieList $movieList): Response
    {
        // Verificar se a lista é pública
        if (!$movieList->is_public) {
            abort(404);
        }

        return Inertia::render('MovieLists/PublicListDetail', [
            'listId' => $movieList->id,
            'listOwner' => $movieList->user->name ?? 'Usuário'
        ]);
    }

    /**
     ** Remove múltiplos filmes de uma lista
     */
    public function bulkRemoveMovies(MovieList $movieList, Request $request): JsonResponse
    {
        if ($movieList->user_id !== Auth::id()) {
            return response()->json([
                'success' => false,
                'message' => 'Lista não encontrada',
            ], 404);
        }

        $request->validate([
            'movie_ids' => 'required|array',
            'movie_ids.*' => 'required|integer',
        ]);

        $movieIds = $request->input('movie_ids');

        try {
            $removedCount = $this->movieListService->bulkRemoveMoviesFromList($movieList, $movieIds);

            return response()->json([
                'success' => true,
                'message' => "Filmes removidos com sucesso",
                'data' => ['removed_count' => $removedCount],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao remover filmes',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     ** Move múltiplos filmes entre listas
     */
    public function bulkMoveMovies(Request $request): JsonResponse
    {
        $request->validate([
            'movie_ids' => 'required|array',
            'movie_ids.*' => 'required|integer',
            'from_list_id' => 'required|integer',
            'to_list_id' => 'required|integer',
        ]);

        $movieIds = $request->input('movie_ids');
        $fromListId = $request->input('from_list_id');
        $toListId = $request->input('to_list_id');

        $fromList = MovieList::where('id', $fromListId)
            ->where('user_id', Auth::id())
            ->first();

        $toList = MovieList::where('id', $toListId)
            ->where('user_id', Auth::id())
            ->first();

        if (!$fromList || !$toList) {
            return response()->json([
                'success' => false,
                'message' => 'Lista não encontrada',
            ], 404);
        }

        try {
            $movedCount = $this->movieListService->bulkMoveMoviesBetweenLists($fromList, $toList, $movieIds);

            return response()->json([
                'success' => true,
                'message' => "Filmes movidos com sucesso",
                'data' => ['moved_count' => $movedCount],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao mover filmes',
                'error' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     ** Marca múltiplos filmes como assistidos
     */
    public function bulkMarkWatched(Request $request): JsonResponse
    {
        $request->validate([
            'movie_ids' => 'required|array',
            'movie_ids.*' => 'required|integer',
        ]);

        $movieIds = $request->input('movie_ids');
        $user = Auth::user();

        try {
            $addedCount = $this->movieListService->bulkMarkMoviesAsWatched($user, $movieIds);

            return response()->json([
                'success' => true,
                'message' => "Filmes marcados como assistidos",
                'data' => ['added_count' => $addedCount],
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Erro ao marcar filmes como assistidos',
                'error' => $e->getMessage(),
            ], 400);
        }
    }
}
