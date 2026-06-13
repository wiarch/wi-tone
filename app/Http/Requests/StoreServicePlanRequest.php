<?php

namespace App\Http\Requests;

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
            'director_contact_id' => ['nullable', 'integer', Rule::exists('contacts', 'id')->where('user_id', $this->user()->id)],
            'entries' => ['nullable', 'string'],
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
            'director_contact_id' => 'director',
        ];
    }
}
