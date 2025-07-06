<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class SearchController extends Controller
{
    /**
     * Página de busca de filmes
     */
    public function index(Request $request): Response
    {
        return Inertia::render('Search', [
            'q' => $request->get('q', ''),
            'genre' => $request->get('genre') ? (int) $request->get('genre') : null,
            'year' => $request->get('year') ? (int) $request->get('year') : null,
            'sort' => $request->get('sort', 'popularity.desc'),
            'page' => $request->get('page', 1) ? (int) $request->get('page', 1) : 1,
        ]);
    }

    /**
     * Página de gêneros
     */
    public function genres(): Response
    {
        return Inertia::render('Genres');
    }
}
