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

        $this->merge([
            'nombpermiso' => $nom,
            'estadopermiso' => $est,
        ]);
    }

    public function rules(): array
    {
        return [
            'nombpermiso' => ['required', 'string', 'max:160', Rule::unique('permisos', 'nombpermiso')], // [web:129]
            'estadopermiso' => ['required', Rule::in(['A', 'I'])], // [web:155]
        ];
    }

    public function attributes(): array
    {
        return [
            'nombpermiso' => 'nombre del permiso',
            'estadopermiso' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'nombpermiso.unique' => 'Ese permiso ya está registrado.',
            'estadopermiso.in' => 'El estado debe ser A (Activo) o I (Inactivo).',
        ];
    }
}
