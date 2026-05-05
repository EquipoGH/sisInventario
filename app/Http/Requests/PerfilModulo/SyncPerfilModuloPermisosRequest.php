<?php

namespace App\Http\Requests\PerfilModulo;

use Illuminate\Foundation\Http\FormRequest;

class SyncPerfilModuloPermisosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Checkbox list: puede venir vacío
            'permisos' => ['nullable', 'array'],
            'permisos.*' => ['integer', 'distinct', 'exists:permisos,idpermiso'],
        ];
    }

    public function messages(): array
    {
        return [
            'permisos.array' => 'Formato inválido de permisos.',
            'permisos.*.integer' => 'Permiso inválido.',
            'permisos.*.distinct' => 'No repitas permisos.',
            'permisos.*.exists' => 'Uno o más permisos no existen.',
        ];
    }

    protected function prepareForValidation(): void
    {
        // Si llega como string, lo convertimos a array; si no, se queda igual
        $p = $this->input('permisos');
        if (is_string($p)) {
            $this->merge(['permisos' => array_filter(array_map('trim', explode(',', $p)))]);
        }
    }
}
