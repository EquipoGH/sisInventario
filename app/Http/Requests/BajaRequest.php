<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class BajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // El acceso se controla en el controller
    }

    public function rules(): array
    {
        return [
            'id_bien'    => 'required|integer|exists:bien,id_bien',
            'fecha_baja' => 'required|date|before_or_equal:today',
            'motivo_baja' => 'required|string|min:10|max:255',
            'resolucion' => 'nullable|string|max:100',
            'observacion' => 'nullable|string|max:1000',
        ];
    }

    public function messages(): array
    {
        return [
            'id_bien.required'       => 'Debe seleccionar el bien a dar de baja',
            'id_bien.exists'         => 'El bien seleccionado no existe',
            'fecha_baja.required'    => 'La fecha de baja es obligatoria',
            'fecha_baja.date'        => 'La fecha de baja no es válida',
            'fecha_baja.before_or_equal' => 'La fecha de baja no puede ser futura',
            'motivo_baja.required'   => 'El motivo de baja es obligatorio',
            'motivo_baja.min'        => 'El motivo debe tener al menos 10 caracteres',
            'motivo_baja.max'        => 'El motivo no puede exceder 255 caracteres',
            'resolucion.max'         => 'La resolución no puede exceder 100 caracteres',
        ];
    }

    public function attributes(): array
    {
        return [
            'id_bien'    => 'bien',
            'fecha_baja' => 'fecha de baja',
            'motivo_baja' => 'motivo de baja',
            'resolucion' => 'resolución',
            'observacion' => 'observación',
        ];
    }
}
