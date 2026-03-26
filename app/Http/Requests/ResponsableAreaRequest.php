<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

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
            ],
            'periodo_anio' => [
                'required',
                'integer',
                'min:2020',
                'max:2099'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'dni_responsable.required' => 'Debe seleccionar un responsable',
            'dni_responsable.size'     => 'El DNI debe tener 8 dígitos',
            'dni_responsable.exists'   => 'El responsable seleccionado no existe',
            'idarea.required'          => 'Debe seleccionar un área',
            'idarea.integer'           => 'El área seleccionada no es válida',
            'idarea.exists'            => 'El área seleccionada no existe',
            'periodo_anio.required'    => 'Debe seleccionar el período/año',
            'periodo_anio.integer'     => 'El período debe ser un año válido',
            'periodo_anio.min'         => 'El año mínimo es 2020',
            'periodo_anio.max'         => 'El año máximo es 2099',
        ];
    }
}
