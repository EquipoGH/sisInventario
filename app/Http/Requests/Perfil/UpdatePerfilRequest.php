<?php

namespace App\Http\Requests\Perfil;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdatePerfilRequest extends FormRequest
{
    public function authorize(): bool
    {

        return true;
    }

    protected function prepareForValidation(): void
    {
        $nombre = (string) $this->input('nomperfil', '');
        $nombre = trim($nombre);
        $nombre = preg_replace('/\s+/', ' ', $nombre);

        $this->merge([
            'nomperfil' => $nombre,
        ]);
    }

    public function rules(): array
    {

        $id = $this->route('perfil')?->idperfil;

        return [
            'nomperfil' => [
                'required',
                'string',
                'max:160',
                Rule::unique('perfil', 'nomperfil')->ignore($id, 'idperfil'),
            ],
        ]; 
    }

    public function attributes(): array
    {
        return [
            'nomperfil' => 'nombre del perfil',
        ];
    }

    public function messages(): array
    {
        return [
            'nomperfil.unique' => 'Ese perfil ya está registrado.',
        ];
    }
}
