<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateSystemSettingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // luego lo cambias a permiso/rol admin
    }

    public function rules(): array
    {
        return [
            // Identidad institución / Branding
            'nombre_institucion' => ['bail', 'required', 'string', 'max:120'],
            'slogan' => ['nullable', 'string', 'max:120'],
            'ruc' => ['nullable', 'string', 'max:30'],

            // Mejor que string: valida formato URL (http/https)
            'web' => ['nullable', 'url', 'max:150'],

            // Contacto
            'direccion' => ['nullable', 'string', 'max:200'],
            'telefono' => ['nullable', 'string', 'max:30'],
            'email_contacto' => ['nullable', 'email', 'max:120'],

            // Regional
            'moneda' => ['required', 'string', 'max:10'],

            // Mejor que string: valida que sea timezone real
            'timezone' => ['required', 'timezone'],

            'locale' => ['required', 'string', 'max:15'],
            'date_format' => ['required', 'string', 'max:20'],

            // Reportes
            'pie_reportes' => ['nullable', 'string', 'max:500'],
            'texto_legal' => ['nullable', 'string', 'max:1000'],

            // Soporte
            'correo_soporte' => ['nullable', 'email', 'max:120'],
            'telefono_soporte' => ['nullable', 'string', 'max:30'],

            // Sistema
            'items_por_pagina' => ['required', 'integer', 'min:1', 'max:500'],

            // AdminLTE
            'sidebar_theme' => ['required', 'in:sidebar-dark-primary,sidebar-dark-success,sidebar-dark-info,sidebar-light-primary'],

            // Archivos
            'logo' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
                'dimensions:min_width=64,min_height=64,max_width=2000,max_height=2000',
            ],
            // Favicon: si aceptas .ico, deja file+mimes (image puede fallar con ico)
            'favicon' => ['nullable', 'file', 'mimes:png,ico', 'max:1024'],

            // Reportes: pon mínimo razonable (para que no suban uno enano y se vea feo en PDF)
            'logo_reportes' => [
                'nullable',
                'image',
                'mimes:png,jpg,jpeg,webp',
                'max:2048',
                'dimensions:min_width=200,min_height=80,max_width=4000,max_height=2000',
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'web.url' => 'La web debe ser una URL válida (ej: https://...).',
            'timezone.timezone' => 'La zona horaria no es válida (ej: America/Lima).',

            'sidebar_theme.in' => 'Tema de sidebar inválido.',
            'items_por_pagina.integer' => 'Items por página debe ser un número.',
            'items_por_pagina.min' => 'Items por página debe ser al menos 1.',
            'items_por_pagina.max' => 'Items por página no puede superar 500.',

            'logo.dimensions' => 'El logo debe tener un tamaño razonable (mín 64x64 y máx 2000x2000).',
            'logo_reportes.dimensions' => 'El logo de reportes debe tener un tamaño mínimo (mín 200x80) para verse bien en PDF.',

            'favicon.mimes' => 'El favicon debe ser PNG o ICO.',
        ];
    }
}
