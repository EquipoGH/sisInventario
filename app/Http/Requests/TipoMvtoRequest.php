<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class TipoMvtoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // Obtener el ID del registro que se está editando (si existe)
        $tipoMvtoId = $this->route('tipoMvto') ? $this->route('tipoMvto')->id_tipo_mvto : null;

        return [
            'tipo_mvto' => [
                'required',
                'string',
                'max:20',
                Rule::unique('tipo_mvto', 'tipo_mvto')->ignore($tipoMvtoId, 'id_tipo_mvto')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_mvto.required' => 'El tipo de movimiento es obligatorio',
            'tipo_mvto.string' => 'El tipo debe ser texto',
            'tipo_mvto.max' => 'El tipo no puede exceder 20 caracteres',
            'tipo_mvto.unique' => 'Este tipo de movimiento ya existe en el sistema'
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_mvto' => 'tipo de movimiento'
        ];
    }
}
