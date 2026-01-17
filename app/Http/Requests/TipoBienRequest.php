<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoBienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtener el ID del registro que se está editando (si existe)
        $tipoBienId = $this->route('tipoBien') ? $this->route('tipoBien')->id_tipo_bien : null;

        return [
            'nombre_tipo' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tipo_bien', 'nombre_tipo')->ignore($tipoBienId, 'id_tipo_bien')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_tipo.required' => 'El nombre del tipo de bien es obligatorio',
            'nombre_tipo.string' => 'El nombre debe ser texto',
            'nombre_tipo.max' => 'El nombre no puede exceder 20 caracteres',
            'nombre_tipo.unique' => 'Este tipo de bien ya existe en el sistema'
        ];
    }

    public function attributes(): array
    {
        return [
            'nombre_tipo' => 'nombre del tipo'
        ];
    }
}
