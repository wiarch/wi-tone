<?php

namespace App\Http\Requests;

use App\Support\WorshipPlan;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreServicePlanRequest extends FormRequest
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
            'date' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:2000'],
            'members' => ['nullable', 'array'],
            'members.*.name' => ['nullable', 'string', 'max:100'],
            'members.*.voice_tone' => ['nullable', 'required_with:members.*.name', 'string', Rule::in(WorshipPlan::VOICE_TONES)],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function attributes(): array
    {
        return [
            'title' => 'título',
            'date' => 'fecha',
            'notes' => 'notas',
            'members.*.name' => 'nombre del integrante',
            'members.*.voice_tone' => 'tono de voz',
        ];
    }
}
