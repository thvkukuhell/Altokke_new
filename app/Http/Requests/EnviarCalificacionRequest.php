<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnviarCalificacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'pasajero';
    }

    public function rules(): array
    {
        return [
            'viaje_id' => ['required', 'integer', 'min:1'],
            'conductor_id' => ['required', 'integer', 'min:1'],
            'estrellas' => ['required', 'integer', 'min:1', 'max:5'],
            'comentario' => ['nullable', 'string', 'max:500'],
        ];
    }
}
