<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class EstadoConservacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $id = $this->route('estadoConservacion')
            ? $this->route('estadoConservacion')->id_estado_conservacion
            : null;

        return [
            'nombre_conservacion' => [
                'required',
                'string',
                'max:50',
                Rule::unique('estado_conservacion', 'nombre_conservacion')->ignore($id, 'id_estado_conservacion'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_conservacion.required' => 'El nombre del estado de conservación es obligatorio',
            'nombre_conservacion.unique'   => 'Este estado de conservación ya existe',
            'nombre_conservacion.max'      => 'El nombre no puede exceder 50 caracteres',
        ];
    }
}
