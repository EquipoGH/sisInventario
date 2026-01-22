<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ResponsableRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $dniActual = $this->route('responsable')
            ? $this->route('responsable')->dni_responsable
            : null;

        return [
            'dni_responsable' => [
                'required',
                'string',
                'size:8',
                'regex:/^[0-9]{8}$/',
                Rule::unique('responsable', 'dni_responsable')->ignore($dniActual, 'dni_responsable')
            ],
            'nombre_responsable' => [
                'required',
                'string',
                'max:20',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'apellidos_responsable' => [
                'required',
                'string',
                'max:20',
                'regex:/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/'
            ],
            'cargo_responsable' => [
                'required',
                'string',
                'max:20'
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'dni_responsable.required' => 'El DNI es obligatorio',
            'dni_responsable.size' => 'El DNI debe tener exactamente 8 dígitos',
            'dni_responsable.regex' => 'El DNI solo debe contener números',
            'dni_responsable.unique' => 'Este DNI ya está registrado',

            'nombre_responsable.required' => 'El nombre es obligatorio',
            'nombre_responsable.max' => 'El nombre no puede exceder 20 caracteres',
            'nombre_responsable.regex' => 'El nombre solo debe contener letras',

            'apellidos_responsable.required' => 'Los apellidos son obligatorios',
            'apellidos_responsable.max' => 'Los apellidos no pueden exceder 20 caracteres',
            'apellidos_responsable.regex' => 'Los apellidos solo deben contener letras',

            'cargo_responsable.required' => 'El cargo es obligatorio',
            'cargo_responsable.max' => 'El cargo no puede exceder 20 caracteres'
        ];
    }
}
