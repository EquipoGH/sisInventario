<?php

namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $name = trim(preg_replace('/\s+/', ' ', (string) $this->input('name', '')));
        $email = strtolower(trim((string) $this->input('email', '')));
        $dni = trim((string) $this->input('dni_usuario', ''));
        $rol = trim((string) $this->input('rol_usuario', ''));
        $estado = strtoupper(trim((string) $this->input('estado_usuario', 'A')));

        $this->merge([
            'name' => $name,
            'email' => $email,
            'dni_usuario' => $dni === '' ? null : $dni,
            'rol_usuario' => $rol,
            'estado_usuario' => in_array($estado, ['A','I'], true) ? $estado : 'A',
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')], // [web:155]
            'password' => ['required', 'confirmed', Password::defaults()], // [web:256]

            'dni_usuario' => ['nullable', 'digits:8', Rule::unique('users', 'dni_usuario')],

            'rol_usuario' => ['required', Rule::in(['ADMIN','USUARIO','INVITADO'])],
            'estado_usuario' => ['required', Rule::in(['A','I'])], // [web:155]

        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nombre',
            'email' => 'correo',
            'password' => 'contraseña',
            'dni_usuario' => 'DNI',
            'rol_usuario' => 'rol',
            'estado_usuario' => 'estado',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => 'Ese correo ya está registrado.',
            'dni_usuario.unique' => 'Ese DNI ya está registrado.',
            'estado_usuario.in' => 'El estado debe ser A (Activo) o I (Inactivo).',
        ];
    }
}
