<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DetalleInventarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'id_inventario'          => [$this->isMethod('post') ? 'required' : 'nullable', 'integer', 'exists:inventario,id_inventario'],
            'id_movimiento'          => [$this->isMethod('post') ? 'required' : 'nullable', 'integer', 'exists:movimiento,id_movimiento'],
            'estado_conservacion'    => ['required', 'integer', 'exists:estado_conservacion,id_estado_conservacion'],
            'observacion'            => ['nullable', 'string', 'max:1000'],
            'estadoverificacion'     => ['nullable', 'string', 'in:verificado,no_encontrado,pendiente,observado'],
            'ubicaciondetectada'     => ['nullable', 'integer', 'exists:ubicacion,id_ubicacion'],
            'usuarioverificador'     => ['nullable', 'integer', 'exists:users,id'],
            'fechaverificacion'      => ['nullable', 'date'],
            'requiereregularizacion' => ['sometimes', 'boolean'],
            'evidencia'              => ['nullable', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return [
            'id_inventario.required'       => 'El inventario es obligatorio.',
            'id_inventario.exists'         => 'El inventario seleccionado no existe.',
            'id_movimiento.required'       => 'El bien (movimiento) es obligatorio.',
            'id_movimiento.exists'         => 'El movimiento seleccionado no existe.',
            'estado_conservacion.required' => 'El estado de conservación es obligatorio.',
            'estado_conservacion.exists'   => 'El estado de conservación seleccionado no existe.',
            'estadoverificacion.in'        => 'El estado de verificación debe ser: verificado, no_encontrado, pendiente u observado.',
            'ubicaciondetectada.exists'    => 'La ubicación detectada no existe.',
            'usuarioverificador.exists'    => 'El usuario verificador no existe.',
            'fechaverificacion.date'       => 'La fecha de verificación no es válida.',
            'observacion.max'              => 'La observación no puede exceder 1000 caracteres.',
            'evidencia.max'                => 'La evidencia no puede exceder 255 caracteres.',
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'requiereregularizacion' => $this->boolean('requiereregularizacion'),
        ]);
    }
}
