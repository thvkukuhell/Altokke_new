<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CompletarViajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'conductor';
    }

    public function rules(): array
    {
        return [
            'id_viaje' => ['required', 'integer', 'min:1'],
        ];
    }
}
