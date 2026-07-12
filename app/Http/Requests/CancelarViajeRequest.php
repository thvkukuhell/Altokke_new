<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CancelarViajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        return [
            'viaje_id' => ['required', 'integer', 'min:1'],
            'motivo_cancelacion' => ['required', 'in:demora_conductor,pasajero_no_en_punto,ubicacion_incorrecta,cambio_opinion,problemas_vehiculo,otro'],
            'motivo_cancelacion_otro' => ['nullable', 'required_if:motivo_cancelacion,otro', 'string', 'min:10', 'max:1000'],
        ];
    }

    public function messages(): array
    {
        return [
            'motivo_cancelacion_otro.required_if' => 'Describe brevemente el motivo de la cancelación.',
        ];
    }
}
