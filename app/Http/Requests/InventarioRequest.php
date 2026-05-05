<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class InventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $inventarioId = $this->route('inventario')
            ? $this->route('inventario')->id_inventario
            : null;

        return [
            'fecha_inicio'     => ['required', 'date'],
            'fecha_fin'        => ['nullable', 'date', 'after_or_equal:fecha_inicio'],
            'responsable'      => ['required', 'string', 'max:8', 'exists:responsable,dni_responsable'],
            'observacion'      => ['nullable', 'string', 'max:1000'],
            'tipoinventario'   => ['required', 'string', 'in:Inventario Físico Anual,Inventario por Cambio de Personal,Inventario por Transferencia,Inventario de Verificación,Inventario de Baja,Inventario Sorpresa'],
            'estadoinventario' => ['nullable', 'string', 'in:pendiente,en_proceso,cerrado,anulado'],
            'codigoinventario' => [
                'nullable',
                'string',
                'max:20',
                Rule::unique('inventario', 'codigoinventario')->ignore($inventarioId, 'id_inventario'),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio.required'        => 'La fecha de inicio es obligatoria.',
            'fecha_inicio.date'            => 'La fecha de inicio no es válida.',
            'fecha_fin.date'               => 'La fecha de fin no es válida.',
            'fecha_fin.after_or_equal'     => 'La fecha de fin debe ser igual o posterior a la fecha de inicio.',
            'responsable.required'         => 'El responsable es obligatorio.',
            'responsable.exists'           => 'El responsable seleccionado no existe en el sistema.',
            'responsable.max'              => 'El DNI del responsable no puede exceder 8 caracteres.',
            'observacion.max'              => 'La observación no puede exceder 1000 caracteres.',
            'tipoinventario.required'       => 'El tipo de inventario es obligatorio.',
            'tipoinventario.in'            => 'Seleccione un tipo de inventario válido.',
            'estadoinventario.in'          => 'El estado no es válido.',
            'codigoinventario.unique'      => 'Este código de inventario ya está registrado.',
            'codigoinventario.max'         => 'El código no puede exceder 20 caracteres.',
        ];
    }
}
