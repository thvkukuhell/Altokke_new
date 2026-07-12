<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ActualizarConductorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'conductor';
    }

    public function rules(): array
    {
        return [
            'licencia_numero' => ['sometimes', 'string', 'max:80'],
            'lat_actual' => ['sometimes', 'nullable', 'numeric', 'between:-90,90'],
            'lng_actual' => ['sometimes', 'nullable', 'numeric', 'between:-180,180'],
        ];
    }
}
