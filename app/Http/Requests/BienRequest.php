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

    protected function prepareForValidation()
    {
        // ⭐ Convertir id_documento vacío a null
        if ($this->id_documento === '' || $this->id_documento === '0') {
            $this->merge(['id_documento' => null]);
        }

        // ⭐ Convertir NumDoc vacío a null
        if ($this->NumDoc === '') {
            $this->merge(['NumDoc' => null]);
        }
    }

    public function rules(): array
    {
        $bienId = $this->route('bien') ? $this->route('bien')->id_bien : null;

        return [
            // ==================== CAMPOS OBLIGATORIOS ====================
            'codigo_patrimonial' => [
                'required',
                'string',
                'max:20',
                Rule::unique('bien', 'codigo_patrimonial')->ignore($bienId, 'id_bien')
            ],
            'denominacion_bien' => 'required|string|max:100',
            'id_tipobien' => 'required|exists:tipo_bien,id_tipo_bien',
            'fecha_registro' => 'required|date|before_or_equal:today',

            // ⭐ Documento Sustento (OPCIONAL)
            'id_documento' => [
                'nullable',
                'integer',
                'exists:documento_sustento,id_documento'
            ],

            // ⭐⭐⭐ NumDoc MANUAL (OPCIONAL) ⭐⭐⭐
            'NumDoc' => 'nullable|string|max:50',

            // ==================== CAMPOS OPCIONALES ====================
            'modelo_bien' => 'nullable|string|max:20',
            'marca_bien' => 'nullable|string|max:20',
            'color_bien' => 'nullable|string|max:20',
            'dimensiones_bien' => 'nullable|string|max:50',
            'nserie_bien' => 'nullable|string|max:20',

            // ⭐ Foto
            'foto' => 'nullable|image|mimes:jpeg,jpg,png,gif,webp|max:5120',
        ];
    }

    public function messages(): array
    {
        return [
            'codigo_patrimonial.required' => 'El código patrimonial es obligatorio',
            'codigo_patrimonial.unique' => 'Este código patrimonial ya existe',
            'codigo_patrimonial.max' => 'El código no puede exceder 20 caracteres',

            'denominacion_bien.required' => 'La denominación es obligatoria',
            'denominacion_bien.max' => 'La denominación no puede exceder 100 caracteres',

            'id_tipobien.required' => 'Debe seleccionar un tipo de bien',
            'id_tipobien.exists' => 'El tipo de bien seleccionado no existe',

            'id_documento.integer' => 'El documento sustento no es válido',
            'id_documento.exists' => 'El documento sustento seleccionado no existe',

            'NumDoc.string' => 'El número de documento debe ser texto',
            'NumDoc.max' => 'El número de documento no puede exceder 50 caracteres',

            'fecha_registro.required' => 'La fecha de registro es obligatoria',
            'fecha_registro.date' => 'La fecha no es válida',
            'fecha_registro.before_or_equal' => 'La fecha no puede ser futura',

            'foto.image' => 'El archivo debe ser una imagen',
            'foto.mimes' => 'Solo se permiten imágenes JPG, PNG, GIF o WebP',
            'foto.max' => 'La imagen no puede ser mayor a 5MB',
        ];
    }

    public function attributes(): array
    {
        return [
            'codigo_patrimonial' => 'código patrimonial',
            'denominacion_bien' => 'denominación',
            'id_tipobien' => 'tipo de bien',
            'id_documento' => 'documento sustento',
            'NumDoc' => 'número de documento',
            'fecha_registro' => 'fecha de registro',
            'modelo_bien' => 'modelo',
            'marca_bien' => 'marca',
            'color_bien' => 'color',
            'dimensiones_bien' => 'dimensiones',
            'nserie_bien' => 'número de serie',
            'foto' => 'fotografía'
        ];
    }
}
