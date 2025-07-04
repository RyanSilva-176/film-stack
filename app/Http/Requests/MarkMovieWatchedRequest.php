<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class MarkMovieWatchedRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'tmdb_movie_id' => 'required|integer|min:1',
            'rating' => 'nullable|integer|min:1|max:10',
            'notes' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'tmdb_movie_id.required' => 'O ID do filme é obrigatório',
            'tmdb_movie_id.integer' => 'O ID do filme deve ser um número inteiro',
            'tmdb_movie_id.min' => 'O ID do filme deve ser maior que zero',
            'rating.integer' => 'A avaliação deve ser um número inteiro',
            'rating.min' => 'A avaliação deve ser entre 1 e 10',
            'rating.max' => 'A avaliação deve ser entre 1 e 10',
            'notes.max' => 'As notas não podem ter mais de 1000 caracteres',
        ];
    }
}
