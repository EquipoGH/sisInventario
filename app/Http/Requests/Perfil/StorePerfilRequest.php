<?php

namespace App\Http\Requests\Perfil;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StorePerfilRequest extends FormRequest
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
        return [
            'nomperfil' => [
                'required',
                'string',
                'max:160',
                Rule::unique('perfil', 'nomperfil'),
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
