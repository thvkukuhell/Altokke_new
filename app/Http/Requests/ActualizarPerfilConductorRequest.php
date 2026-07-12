<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarPerfilConductorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->tipo_usuario === 'conductor';
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'telefono' => preg_replace('/\s+/', '', trim((string) $this->input('telefono'))),
            'email' => trim((string) $this->input('email')),
        ]);
    }

    public function rules(): array
    {
        $userId = $this->user()?->getKey();

        return [
            'nombre_completo' => ['required', 'string', 'max:150'],
            'apellidos' => ['nullable', 'string', 'max:150'],
            'telefono' => [
                'required',
                'string',
                'regex:/^[0-9]{9}$/',
                Rule::unique('usuarios', 'telefono')->ignore($userId, 'id_usuario'),
            ],
            'email' => [
                'required',
                'email',
                'max:150',
                Rule::unique('usuarios', 'email')->ignore($userId, 'id_usuario'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'telefono.required' => 'El teléfono es obligatorio.',
            'telefono.regex' => 'El teléfono debe contener 9 dígitos.',
            'telefono.unique' => 'El teléfono ya está registrado.',
            'email.unique' => 'El email ya está en uso.',
        ];
    }
}
