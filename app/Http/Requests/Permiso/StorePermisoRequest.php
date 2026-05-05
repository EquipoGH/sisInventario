<?php

namespace App\Http\Requests\Permiso;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePermisoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nom = trim(preg_replace('/\s+/', ' ', (string) $this->input('nombpermiso', '')));
        $est = strtoupper(trim((string) $this->input('estadopermiso', 'A')));

        $rn = trim((string) $this->input('route_name', ''));
        $rn = preg_replace('/\s+/', '', $rn);

        $this->merge([
            'nombpermiso' => $nom,
            'estadopermiso' => $est,
            'route_name' => $rn === '' ? null : $rn,

            // viene del select (opcional, solo para pivot)
            'idmodulo' => $this->input('idmodulo'),
        ]);
    }

    public function rules(): array
    {
        return [
            'nombpermiso' => ['required', 'string', 'max:160', Rule::unique('permisos', 'nombpermiso')],
            'estadopermiso' => ['required', Rule::in(['A', 'I'])],

            // NUEVO (se guarda en permisos)
            'route_name' => ['nullable', 'string', 'max:120'],

            // NO se guarda en permisos (solo pivot admin)
            'idmodulo' => ['nullable', 'integer', 'exists:modulos,idmodulo'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nombpermiso' => 'nombre del permiso',
            'estadopermiso' => 'estado',
            'route_name' => 'ruta (route_name)',
            'idmodulo' => 'módulo',
        ];
    }

    public function messages(): array
    {
        return [
            'nombpermiso.unique' => 'Ese permiso ya está registrado.',
            'estadopermiso.in' => 'El estado debe ser A (Activo) o I (Inactivo).',
            'idmodulo.exists' => 'El módulo seleccionado no existe.',
        ];
    }
}
