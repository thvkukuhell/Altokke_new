<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CrearViajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'pasajero';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'origen' => trim((string) $this->input('origen', '')),
            'destino' => trim((string) $this->input('destino', '')),
        ]);
    }

    public function rules(): array
    {
        return [
            'origen' => ['required', 'string', 'min:2', 'max:300', 'not_regex:/^(nan|null|undefined)$/i'],
            'destino' => ['required', 'string', 'different:origen', 'min:2', 'max:300', 'not_regex:/^(nan|null|undefined)$/i'],
            'tipo_servicio' => ['required', 'in:normal,express'],
            'metodo_pago' => ['required', 'in:efectivo,yape,plin'],
            'origen_lat' => ['required', 'numeric', 'between:-90,90'],
            'origen_lng' => ['required', 'numeric', 'between:-180,180'],
            'destino_lat' => ['required', 'numeric', 'between:-90,90'],
            'destino_lng' => ['required', 'numeric', 'between:-180,180'],
            'distancia_km' => ['nullable', 'numeric', 'min:0', 'max:500'],
            'tiempo_min' => ['nullable', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        return [
            'destino.different' => 'El origen y destino no pueden ser iguales.',
            'origen_lat.required' => 'Marca tu origen en el mapa.',
            'origen_lng.required' => 'Marca tu origen en el mapa.',
            'destino_lat.required' => 'Marca tu destino en el mapa.',
            'destino_lng.required' => 'Marca tu destino en el mapa.',
            'origen.not_regex' => 'El origen no es válido.',
            'destino.not_regex' => 'El destino no es válido.',
        ];
    }
}
