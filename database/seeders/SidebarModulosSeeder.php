<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modulo;
use App\Models\Perfil;
use App\Models\PerfilModulo;

class SidebarModulosSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Perfil::where('nomperfil', 'Admin')
            ->orWhere('nomperfil', 'Administrador')
            ->first();

        $modulos = [
            [
                'nommodulo'    => 'Gestión De Bienes',
                'etiqueta'     => 'BIENES',
                'color'        => '#17a2b8',
                'icono'        => 'fas fa-boxes',
                'route_prefix' => 'bien.*,movimiento.*,baja.*',
                'estadomodulo' => 'A',
            ],
            [
                'nommodulo'    => 'Documento Sustento',
                'etiqueta'     => 'DOCUMENTOS',
                'color'        => '#fd7e14',
                'icono'        => 'fas fa-file-alt',
                'route_prefix' => 'documento-sustento.*',
                'estadomodulo' => 'A',
            ],
            [
                'nommodulo'    => 'Catálogos',
                'etiqueta'     => 'CATALOGOS',
                'color'        => '#6f42c1',
                'icono'        => 'fas fa-layer-group',
                'route_prefix' => 'catalogos.*',
                'estadomodulo' => 'A',
            ],
            [
                'nommodulo'    => 'Reportes',
                'etiqueta'     => 'REPORTES',
                'color'        => '#20c997',
                'icono'        => 'fas fa-chart-bar',
                'route_prefix' => 'reportes.*,qr-bienes.*',
                'estadomodulo' => 'A',
            ],
            [
                'nommodulo'    => 'Control De Inventario',
                'etiqueta'     => 'INVENTARIO',
                'color'        => '#e83e8c',
                'icono'        => 'fas fa-clipboard-list',
                'route_prefix' => 'inventario.*',
                'estadomodulo' => 'A',
            ],
            [
                'nommodulo'    => 'Seguridad',
                'etiqueta'     => 'SEGURIDAD',
                'color'        => '#dc3545',
                'icono'        => 'fas fa-shield-alt',
                'route_prefix' => 'user.*,perfil.*,permiso.*,modulo.*,users.*,perfil-modulo.*',
                'estadomodulo' => 'A',
            ],
            [
                'nommodulo'    => 'Configuración',
                'etiqueta'     => 'CONFIG',
                'color'        => '#6c757d',
                'icono'        => 'fas fa-cog',
                'route_prefix' => 'configuracion.*',
                'estadomodulo' => 'A',
            ],
        ];

        foreach ($modulos as $data) {
            $m = Modulo::updateOrCreate(
                ['nommodulo' => $data['nommodulo']],
                [
                    'etiqueta'     => $data['etiqueta'],
                    'color'        => $data['color'],
                    'icono'        => $data['icono'],
                    'route_prefix' => $data['route_prefix'],
                    'estadomodulo' => $data['estadomodulo'],
                ]
            );

            if ($admin) {
                PerfilModulo::firstOrCreate([
                    'idperfil' => $admin->idperfil,
                    'idmodulo' => $m->idmodulo,
                ]);
            }
        }
    }
}
