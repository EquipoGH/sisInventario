<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use App\Models\ResponsableArea;

class ResponsableAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'dni_responsable' => [
                'required',
                'string',
                'size:8',
                'exists:responsable,dni_responsable'
            ],
            'idarea' => [
                'required',
                'integer',
                'exists:area,id_area'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'dni_responsable.required' => 'Debe seleccionar un responsable',
            'dni_responsable.size' => 'El DNI debe tener 8 dígitos',
            'dni_responsable.exists' => 'El responsable seleccionado no existe',

            'idarea.required' => 'Debe seleccionar un área',
            'idarea.integer' => 'El área seleccionada no es válida',
            'idarea.exists' => 'El área seleccionada no existe'
        ];
    }

    /**
     * Validación adicional después de las reglas básicas
     */
    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            // Verificar si ya existe la asignación
            if (ResponsableArea::existeAsignacion(
                $this->dni_responsable,
                $this->idarea
            )) {
                $validator->errors()->add(
                    'duplicado',
                    'Este responsable ya está asignado a esta área'
                );
            }
        });
    }
}
