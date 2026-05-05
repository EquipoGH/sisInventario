<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class DocumentoSustentoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        // ⭐ CORREGIDO: Nombre del parámetro de ruta debe coincidir con web.php
        $documentoId = $this->route('documento_sustento')
            ? $this->route('documento_sustento')->id_documento
            : null;

        return [
            'tipo_documento' => [
                'required',
                'string',
                'max:50' // ⭐ CORREGIDO: De 20 a 50 (según migración)
            ],
            'numero_documento' => [
                'required',
                'string',
                'max:20',
                Rule::unique('documento_sustento', 'numero_documento')
                    ->ignore($documentoId, 'id_documento')
            ],
            'fecha_documento' => [
                'required',
                'date',
                'before_or_equal:today'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'tipo_documento.required' => 'El tipo de documento es obligatorio',
            'tipo_documento.max' => 'El tipo no puede exceder 50 caracteres', // ⭐ ACTUALIZADO

            'numero_documento.required' => 'El número de documento es obligatorio',
            'numero_documento.max' => 'El número no puede exceder 20 caracteres',
            'numero_documento.unique' => 'Este número de documento ya está registrado',

            'fecha_documento.required' => 'La fecha es obligatoria',
            'fecha_documento.date' => 'La fecha no es válida',
            'fecha_documento.before_or_equal' => 'La fecha no puede ser futura'
        ];
    }

    public function attributes(): array
    {
        return [
            'tipo_documento' => 'tipo de documento',
            'numero_documento' => 'número de documento',
            'fecha_documento' => 'fecha de documento'
        ];
    }
}
