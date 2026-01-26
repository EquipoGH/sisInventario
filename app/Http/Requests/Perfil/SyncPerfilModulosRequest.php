<?php

namespace App\Http\Requests\Perfil;

use Illuminate\Foundation\Http\FormRequest;

class SyncPerfilModulosRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Pro: aquí puedes usar policies/roles si ya lo tienes.
        // return $this->user()->can('update', $this->route('perfil'));
        return true;
    }

    public function rules(): array
    {
        return [
            'modulos' => ['nullable', 'array'],
            'modulos.*' => ['integer', 'distinct', 'exists:modulos,idmodulo'],
        ];
    }

    public function messages(): array
    {
        return [
            'modulos.array' => 'El campo módulos debe ser una lista.',
            'modulos.*.integer' => 'Cada módulo debe ser un ID numérico.',
            'modulos.*.distinct' => 'No repitas módulos.',
            'modulos.*.exists' => 'Uno de los módulos no existe.',
        ];
    }
}
