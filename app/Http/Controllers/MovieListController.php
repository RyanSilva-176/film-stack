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
        $moviesData = $this->movieListService->getListMoviesWithDetails($movieList, $page);

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
}
