<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPasajeroPerfilRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'pasajero';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telefono' => preg_replace('/\s+/', '', trim((string) $this->input('telefono'))),
            'dni' => preg_replace('/\s+/', '', trim((string) $this->input('dni'))),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'dni' => [
                'nullable',
                'regex:/^[0-9]{8}$/',
                Rule::unique('usuarios', 'dni')->ignore($userId, 'id_usuario'),
            ],
            'telefono' => [
                'required',
                'string',
                'regex:/^[0-9]{9}$/',
                Rule::unique('usuarios', 'telefono')->ignore($userId, 'id_usuario'),
            ],
            'metodo_pago_preferido' => ['required', 'in:efectivo,yape,plin'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.regex' => 'El DNI debe contener 8 dígitos.',
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe contener 9 dígitos.',
            'telefono.unique' => 'El teléfono ya está registrado.',
        ];
    }
}
