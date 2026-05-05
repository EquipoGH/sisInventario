<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class SyncUserPerfilesRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Ajusta esto a tu lógica (rol, permiso, policy, etc.)
        return true;
    }

    public function rules(): array
    {
        return [
            'perfiles' => ['nullable', 'array'],
            'perfiles.*' => ['integer', 'distinct', 'exists:perfil,idperfil'],
        ];
    }

    public function messages(): array
    {
        return [
            'perfiles.array' => 'El formato de perfiles no es válido.',
            'perfiles.*.integer' => 'Cada perfil debe ser un ID numérico.',
            'perfiles.*.distinct' => 'Hay perfiles repetidos.',
            'perfiles.*.exists' => 'Uno o más perfiles no existen.',
        ];
    }
}
