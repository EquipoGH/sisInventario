<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class AreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $areaId = $this->route('area') ? $this->route('area')->id_area : null;

        return [
            'nombre_area' => [
                'required',
                'string',
                'max:100',
                Rule::unique('area', 'nombre_area')->ignore($areaId, 'id_area')
            ]
        ];
    }

    public function messages(): array
    {
        return [
            'nombre_area.required' => 'El nombre del área es obligatorio',
            'nombre_area.max' => 'El nombre no puede exceder 100 caracteres',
            'nombre_area.unique' => 'Esta área ya existe'
        ];
    }
}
