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

        $ico = trim((string) $this->input('icono', ''));
        $ico = preg_replace('/\s+/', ' ', $ico);

        $rp = trim((string) $this->input('route_prefix', ''));
        $rp = preg_replace('/\s+/', '', $rp);

        $this->merge([
            'nommodulo' => $nom,
            'estadomodulo' => $est,
            'etiqueta' => $eti === '' ? null : $eti,
            'color' => $col === '' ? null : $col,
            'icono' => $ico === '' ? null : $ico,
            'route_prefix' => $rp === '' ? null : $rp,
        ]);
    }

    public function rules(): array
    {
        $id = $this->route('modulo')?->idmodulo;

        return [
            'nommodulo' => [
                'required', 'string', 'max:150',
                Rule::unique('modulos', 'nommodulo')->ignore($id, 'idmodulo'),
            ],
            'estadomodulo' => ['required', Rule::in(['A', 'I'])],
            'etiqueta' => ['nullable', 'string', 'max:30'],
            'color' => ['nullable', 'string', 'max:12'],
            'icono' => ['nullable', 'string', 'max:80'],
            'route_prefix' => ['nullable', 'string', 'max:2000', 'regex:/^[A-Za-z0-9\-\.\,\*]+$/'],
        ];
    }

    public function attributes(): array
    {
        return [
            'nommodulo' => 'nombre del módulo',
            'estadomodulo' => 'estado',
            'etiqueta' => 'etiqueta',
            'color' => 'color',
            'icono' => 'ícono',
            'route_prefix' => 'prefijo de ruta',
        ];
    }

    public function messages(): array
    {
        return [
            'nommodulo.unique' => 'Ese módulo ya está registrado.',
            'estadomodulo.in' => 'El estado debe ser A (Activo) o I (Inactivo).',
            'route_prefix.regex' => 'El prefijo solo acepta letras, números, guiones, puntos, comas y * (ej: user.*,permiso.*).',
        ];
    }
}
