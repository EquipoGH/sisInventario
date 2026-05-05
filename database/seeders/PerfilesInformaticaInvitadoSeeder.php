<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Modulo;
use App\Models\Permiso;
use App\Models\Perfil;
use App\Models\PerfilModulo;
use App\Models\ModuloPermiso;

class PerfilesInformaticaInvitadoSeeder extends Seeder
{
    public function run(): void
    {
        $mapaPerfiles = [
            'Informática' => [
                'Gestión De Bienes' => [
                    ['label' => 'Registrar Bien', 'route' => 'bien.index'],
                    ['label' => 'Movimientos', 'route' => 'movimiento.index'],
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
                    ['label' => 'Estado Conservación', 'route' => 'catalogos.estado-conservacion.index'],
                ],
                'Reportes' => [
                    ['label' => 'Reporte de Bienes', 'route' => 'reportes.bienes.index'],
                    ['label' => 'Generador Masivo QR', 'route' => 'qr-bienes.index'],
                ],
            ],

            'Invitado' => [
                'Gestión De Bienes' => [
                    ['label' => 'Registrar Bien', 'route' => 'bien.index'],
                ],
                'Documento Sustento' => [
                    ['label' => 'Registrar Documento', 'route' => 'documento-sustento.index'],
                ],
                'Reportes' => [
                    ['label' => 'Reporte de Bienes', 'route' => 'reportes.bienes.index'],
                    ['label' => 'Generador Masivo QR', 'route' => 'qr-bienes.index'],
                ],
            ],
        ];

        foreach ($mapaPerfiles as $nombrePerfil => $modulos) {
            $perfil = Perfil::where('nomperfil', $nombrePerfil)->first();

            if (!$perfil) {
                continue;
            }

            foreach ($modulos as $nomModulo => $permisos) {
                $modulo = Modulo::where('nommodulo', $nomModulo)->first();

                if (!$modulo) {
                    continue;
                }

                $perfilModulo = PerfilModulo::firstOrCreate([
                    'idperfil' => $perfil->idperfil,
                    'idmodulo' => $modulo->idmodulo,
                ]);

                ModuloPermiso::where('idperfilmodulo', $perfilModulo->idperfilmodulo)->delete();

                foreach ($permisos as $p) {
                    $label = trim((string) ($p['label'] ?? ''));
                    $route = isset($p['route']) ? trim((string) $p['route']) : null;
                    $route = ($route === '') ? null : $route;

                    if ($label === '') {
                        continue;
                    }

                    $permiso = Permiso::updateOrCreate(
                        ['nombpermiso' => $label],
                        [
                            'estadopermiso' => 'A',
                            'route_name' => $route,
                        ]
                    );

                    ModuloPermiso::firstOrCreate([
                        'idperfilmodulo' => $perfilModulo->idperfilmodulo,
                        'idpermiso' => $permiso->idpermiso,
                    ]);
                }
            }
        }
    }
}