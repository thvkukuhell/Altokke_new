<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class EnviarConsultaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'nombre' => ['required', 'string', 'max:150'],
            'correo' => ['required', 'email', 'max:150'],
            'asunto' => ['required', 'string', 'max:150'],
            'tipo_solicitud' => ['required', 'in:consulta,reclamo,sugerencia,reporte'],
            'descripcion' => ['required', 'string', 'max:1200'],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre.required' => 'El nombre es obligatorio.',
            'correo.required' => 'El correo es obligatorio.',
            'correo.email' => 'El correo debe ser válido.',
            'asunto.required' => 'El asunto es obligatorio.',
            'tipo_solicitud.required' => 'Selecciona el tipo de solicitud.',
            'descripcion.required' => 'Ingresa una descripción del problema o reclamo.',
        ];
    }
}
