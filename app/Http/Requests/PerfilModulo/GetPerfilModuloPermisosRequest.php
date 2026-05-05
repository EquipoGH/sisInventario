<?php

namespace App\Http\Requests\PerfilModulo;

use Illuminate\Foundation\Http\FormRequest;

class GetPerfilModuloPermisosRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Aquí puedes conectar tu sistema de permisos (Gate/Policy) si ya lo tienes
        return true;
    }

    public function rules(): array
    {
        return [];
    }
}
