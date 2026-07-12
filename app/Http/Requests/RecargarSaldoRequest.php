<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class RecargarSaldoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'conductor';
    }

    public function rules(): array
    {
        return [
            'monto' => ['required', 'numeric', 'min:5', 'max:500'],
            'metodo_recarga' => ['required', 'in:yape,plin,efectivo'],
            'referencia' => ['nullable', 'string', 'max:150'],
        ];
    }
}
