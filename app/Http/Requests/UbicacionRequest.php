<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UbicacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $ubicacionId = $this->route('ubicacion')
            ? $this->route('ubicacion')->id_ubicacion
            : null;

        return [
            'nombre_sede' => [
                'required',
                'string',
                'max:100'
            ],
            'ambiente' => [
                'required',
                'string',
                'max:100'
            ],
            'piso_ubicacion' => [
                'required',
                'string',
                'max:100'
            ],
            'idarea' => [
                'required',
                'integer',
                'exists:area,id_area'
            ],
            // ⭐⭐⭐ NUEVO CAMPO ⭐⭐⭐
            'es_recepcion_inicial' => [
                'nullable',
                'boolean'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_sede.required' => 'El nombre de la sede es obligatorio',
            'nombre_sede.max' => 'El nombre de la sede no puede exceder 100 caracteres',

            'ambiente.required' => 'El ambiente es obligatorio',
            'ambiente.max' => 'El ambiente no puede exceder 100 caracteres',

            'piso_ubicacion.required' => 'El piso es obligatorio',
            'piso_ubicacion.max' => 'El piso no puede exceder 100 caracteres',

            'idarea.required' => 'Debe seleccionar un área',
            'idarea.integer' => 'El área seleccionada no es válida',
            'idarea.exists' => 'El área seleccionada no existe',

            // ⭐ NUEVO MENSAJE
            'es_recepcion_inicial.boolean' => 'El valor de recepción inicial no es válido'
        ];
    }

    /**
     * Preparar datos antes de validación
     */
    protected function prepareForValidation()
    {
        // Convertir a mayúsculas automáticamente
        $this->merge([
            'nombre_sede' => strtoupper($this->nombre_sede ?? ''),
            'ambiente' => strtoupper($this->ambiente ?? ''),
            'piso_ubicacion' => strtoupper($this->piso_ubicacion ?? ''),
            // ⭐ Convertir checkbox a booleano
            'es_recepcion_inicial' => $this->has('es_recepcion_inicial')
                ? filter_var($this->es_recepcion_inicial, FILTER_VALIDATE_BOOLEAN)
                : false
        ]);
    }
}
