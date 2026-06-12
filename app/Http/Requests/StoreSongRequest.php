<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreSongRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'artist' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:10'],
            'guitar_content' => ['nullable', 'string'],
            'keyboard_content' => ['nullable', 'string'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'artist' => 'artista',
            'key' => 'tono',
            'guitar_content' => 'cifrado de guitarra',
            'keyboard_content' => 'cifrado de teclado',
        ];
    }
}
