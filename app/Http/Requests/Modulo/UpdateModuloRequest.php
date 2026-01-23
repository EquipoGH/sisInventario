<?php

namespace App\Http\Requests\Modulo;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateModuloRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $nom = trim(preg_replace('/\s+/', ' ', (string) $this->input('nommodulo', '')));
        $est = strtoupper(trim((string) $this->input('estadomodulo', 'A')));
        $eti = trim((string) $this->input('etiqueta', ''));
        $col = trim((string) $this->input('color', ''));

        $this->merge([
            'nommodulo' => $nom,
            'estadomodulo' => $est,
            'etiqueta' => $eti === '' ? null : $eti,
            'color' => $col === '' ? null : $col,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('modulo')?->idmodulo;

        return [
            'nommodulo' => [
                'required', 'string', 'max:150',
                Rule::unique('modulos', 'nommodulo')->ignore($id, 'idmodulo'), // [web:155]
            ],
            'estadomodulo' => ['required', Rule::in(['A', 'I'])], // [web:155]
            'etiqueta' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:12'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nommodulo' => 'nombre del módulo',
            'estadomodulo' => 'estado',
            'etiqueta' => 'etiqueta',
            'color' => 'color',
        ];
    }

    public function messages(): array
    {
        return [
            'nommodulo.unique' => 'Ese módulo ya está registrado.',
            'estadomodulo.in' => 'El estado debe ser A (Activo) o I (Inactivo).',
        ];
    }
}
