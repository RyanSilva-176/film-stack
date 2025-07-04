<?php

namespace App\Http\Middleware;

use App\Models\MovieList;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class PublicListMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $movieList = $request->route('movieList');
        
        if ($movieList instanceof MovieList && !$movieList->is_public) {
            return response()->json([
                'success' => false,
                'message' => 'Esta lista não é pública',
            ], 403);
        }
        
        return $next($request);
    }
}
