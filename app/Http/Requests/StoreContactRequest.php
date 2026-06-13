<?php

namespace App\Http\Requests;

use App\Support\WorshipPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreContactRequest extends FormRequest
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
            'name' => ['required', 'string', 'max:100'],
            'role' => ['nullable', 'string', 'max:50', Rule::in(WorshipPlan::ROLES)],
            'vocal_range' => ['nullable', 'string', 'max:30', Rule::in(WorshipPlan::VOCAL_RANGES)],
            'vocal_tone' => ['nullable', 'string', 'max:30', Rule::in(WorshipPlan::VOICE_TONES)],
            'email' => ['nullable', 'email', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'photo' => ['nullable', 'image', 'mimes:jpeg,jpg,png,webp', 'max:2048'],
            'remove_photo' => ['nullable', 'boolean'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'role' => 'rol',
            'vocal_range' => 'rango vocal',
            'vocal_tone' => 'tono vocal',
            'email' => 'correo',
            'phone' => 'teléfono',
            'photo' => 'foto',
        ];
    }
}
