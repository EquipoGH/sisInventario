<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstadoBienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtener el ID del registro que se está editando (si existe)
        $estadoId = $this->route('estadoBien') ? $this->route('estadoBien')->id_estado : null;

        return [
            'nombre_estado' => [
                'required',
                'string',
                'max:20',
                Rule::unique('estado_bien', 'nombre_estado')->ignore($estadoId, 'id_estado')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_estado.required' => 'El nombre del estado es obligatorio',
            'nombre_estado.string' => 'El nombre debe ser texto',
            'nombre_estado.max' => 'El nombre no puede exceder 20 caracteres',
            'nombre_estado.unique' => 'Este estado ya existe en el sistema'
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_estado' => 'nombre del estado'
        ];
    }
}
