<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class BienRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $bienId = $this->route('bien') ? $this->route('bien')->id_bien : null;

        return [
            'codigo_patrimonial' => [
                'required',
                'string',
                'max:20',
                Rule::unique('bien', 'codigo_patrimonial')->ignore($bienId, 'id_bien')
            ],
            'denominacion_bien' => 'required|string|max:100',
            'id_tipobien' => 'required|exists:tipo_bien,id_tipo_bien',
            'modelo_bien' => 'nullable|string|max:20',
            'marca_bien' => 'nullable|string|max:20',
            'color_bien' => 'nullable|string|max:20',
            'dimensiones_bien' => 'nullable|string|max:50',
            'nserie_bien' => 'nullable|string|max:20',
            'fecha_registro' => 'required|date',
            'foto_bien' => 'nullable|image|mimes:jpeg,jpg,png,gif|max:5120', // 5MB
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_patrimonial.required' => 'El código patrimonial es obligatorio',
            'codigo_patrimonial.unique' => 'Este código patrimonial ya existe',
            'denominacion_bien.required' => 'La denominación es obligatoria',
            'id_tipobien.required' => 'Debe seleccionar un tipo de bien',
            'id_tipobien.exists' => 'El tipo de bien no existe',
            'fecha_registro.required' => 'La fecha de registro es obligatoria',
            'fecha_registro.date' => 'La fecha no es válida',
            'foto_bien.image' => 'El archivo debe ser una imagen',
            'foto_bien.mimes' => 'Solo se permiten imágenes JPG, PNG o GIF',
            'foto_bien.max' => 'La imagen no puede ser mayor a 5MB'
        ];
    }
}
