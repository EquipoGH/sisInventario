<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Perfil;
use App\Models\PerfilModulo;
use App\Models\ModuloPermiso;

class SidebarPermisosSeeder extends Seeder
{
    public function run(): void
    {
        $admin = Perfil::where('nomperfil', 'Admin')
            ->orWhere('nomperfil', 'Administrador')
            ->first();

        if (!$admin) return;

        $map = [
            'Gestión De Bienes' => [
                ['label' => 'Registrar Bien', 'route' => 'bien.index'],
            ],

            'Movimientos' => [
                ['label' => 'Registrar Movimiento', 'route' => 'movimiento.index'],
            ],

            'Documento Sustento' => [
                ['label' => 'Registrar Documento', 'route' => 'documento-sustento.index'],
            ],

            'Catálogos' => [
                ['label' => 'Tipo de Bien', 'route' => 'catalogos.tipo-bien.index'],
                ['label' => 'Estado del Bien', 'route' => 'catalogos.estado-bien.index'],
                ['label' => 'Tipo de Movimiento', 'route' => 'catalogos.tipo-mvto.index'],
                ['label' => 'Área', 'route' => 'catalogos.area.index'],
                ['label' => 'Ubicación', 'route' => 'catalogos.ubicacion.index'],
                ['label' => 'Responsable', 'route' => 'catalogos.responsable.index'],
                ['label' => 'Asignación', 'route' => 'catalogos.responsable-area.index'],
            ],

            'Reportes' => [
                ['label' => 'Reporte de Bienes', 'route' => 'reportes.bienes.index'],
                ['label' => 'Reporte Movimientos', 'route' => 'reportes.movimientos.index'],
            ],

            'Seguridad' => [
                ['label' => 'Usuarios', 'route' => 'user.index'],
                ['label' => 'Perfiles', 'route' => 'perfil.index'],
                ['label' => 'Permisos', 'route' => 'permiso.index'],
                ['label' => 'Módulos', 'route' => 'modulo.index'],
            ],

            'Configuración' => [
                ['label' => 'Apariencia del Sistema', 'route' => 'configuracion.index'],
                ['label' => 'Institución / Branding', 'route' => 'configuracion.institucion'],
            ],
        ];

        foreach ($map as $nomModulo => $permisos) {

            $mod = Modulo::where('nommodulo', $nomModulo)->first();
            if (!$mod) continue;

            $perfilModulo = PerfilModulo::firstOrCreate([
                'idperfil' => $admin->idperfil,
                'idmodulo' => $mod->idmodulo,
            ]);

            // Limpia pivots actuales para este (perfil, módulo)
            ModuloPermiso::where('idperfilmodulo', $perfilModulo->idperfilmodulo)->delete();

            // Inserta pivots según el mapa
            foreach ($permisos as $p) {
                $label = trim((string)($p['label'] ?? ''));
                $route = isset($p['route']) ? trim((string)$p['route']) : null;
                $route = ($route === '') ? null : $route;

                if ($label === '') continue;

                $perm = Permiso::updateOrCreate(
                    ['nombpermiso' => $label],
                    [
                        'estadopermiso' => 'A',
                        'route_name'    => $route,
                    ]
                );

                ModuloPermiso::create([
                    'idperfilmodulo' => $perfilModulo->idperfilmodulo,
                    'idpermiso'      => $perm->idpermiso,
                ]);
            }
        }
    }
}
