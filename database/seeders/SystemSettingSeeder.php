<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class SystemSettingSeeder extends Seeder
{
    public function run(): void
    {
        $defaults = [
            // Identidad institución
            ['key' => 'nombre_institucion', 'value' => 'Institución', 'type' => 'string'],
            ['key' => 'slogan', 'value' => '', 'type' => 'string'],
            ['key' => 'ruc', 'value' => '', 'type' => 'string'],
            ['key' => 'web', 'value' => '', 'type' => 'string'],

            // Config general
            ['key' => 'items_por_pagina', 'value' => 10, 'type' => 'number'],

            // Branding
            ['key' => 'logo_path', 'value' => null, 'type' => 'image'],
            ['key' => 'logo_reportes_path', 'value' => null, 'type' => 'image'],
            ['key' => 'favicon_path', 'value' => null, 'type' => 'image'],
            ['key' => 'sidebar_theme', 'value' => 'sidebar-dark-primary', 'type' => 'string'],

            // Contacto
            ['key' => 'direccion', 'value' => '', 'type' => 'string'],
            ['key' => 'telefono', 'value' => '', 'type' => 'string'],
            ['key' => 'email_contacto', 'value' => '', 'type' => 'string'],

            // Regional
            ['key' => 'moneda', 'value' => 'PEN', 'type' => 'string'],
            ['key' => 'timezone', 'value' => 'America/Lima', 'type' => 'string'],
            ['key' => 'locale', 'value' => 'es_PE', 'type' => 'string'],
            ['key' => 'date_format', 'value' => 'd/m/Y', 'type' => 'string'],

            // Reportes
            ['key' => 'pie_reportes', 'value' => 'Documento generado por el sistema.', 'type' => 'text'],
            ['key' => 'texto_legal', 'value' => 'Documento de uso interno. La información corresponde a los registros del sistema a la fecha/hora de emisión y puede variar con movimientos posteriores.', 'type' => 'text'],

            // Soporte (institución)
            ['key' => 'correo_soporte', 'value' => '', 'type' => 'string'],
            ['key' => 'telefono_soporte', 'value' => '', 'type' => 'string'],
        ];

        foreach ($defaults as $row) {
            SystemSetting::updateOrCreate(
                ['key' => $row['key']],
                ['value' => $row['value'], 'type' => $row['type']]
            );
        }
    }
}
