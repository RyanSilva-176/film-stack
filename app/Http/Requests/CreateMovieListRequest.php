<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;

class CreateMovieListRequest extends FormRequest
{
    public function authorize(): bool
    {
        return Auth::check();
    }

    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'is_public' => 'boolean',
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'O nome da lista é obrigatório',
            'name.max' => 'O nome da lista não pode ter mais de 255 caracteres',
            'description.max' => 'A descrição não pode ter mais de 1000 caracteres',
        ];
    }
}
