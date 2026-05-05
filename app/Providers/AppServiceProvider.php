<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;

use App\Models\Bien;
use App\Observers\BienObserver;

use App\Models\PerfilModulo;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        // Observer Bien
        Bien::observe(BienObserver::class);

        // Paginación Bootstrap 5
        Paginator::useBootstrapFive();

        // ================================
        // SIDEBAR DINÁMICO (multi-perfil)
        // ================================
        View::composer('layouts.sidebar-dinamico', function ($view) {

            $user = auth()->user();

            if (!$user) {
                $view->with('sidebarMenu', collect());
                return;
            }

            // En tu BD/relación funciona con "perfil.idperfil"
            $perfilIds = $user->perfiles()->pluck('perfil.idperfil');

            if ($perfilIds->isEmpty()) {
                $view->with('sidebarMenu', collect());
                return;
            }

            $perfilModulos = PerfilModulo::query()
                ->whereIn('idperfil', $perfilIds->all())
                ->with([
                    'modulo'   => fn ($q) => $q->active(),
                    'permisos' => fn ($q) => $q->active()->ordered(),
                ])
                ->get()
                ->filter(fn ($pm) => (bool) $pm->modulo)
                ->values();

            $ordenPermisos = [
                'bien.index' => 1,
                'movimiento.index' => 2,
                'baja.index' => 3,
            ];

            $menu = $perfilModulos
                ->groupBy('idmodulo')
                ->map(function ($items) use ($ordenPermisos) {
                    $first = $items->first();

                    $permisos = $items->flatMap(fn ($pm) => $pm->permisos)
                        ->unique('idpermiso')
                        ->sortBy(function ($perm) use ($ordenPermisos) {
                            return $ordenPermisos[$perm->route_name] ?? 999;
                        })
                        ->values();

                    $first->setRelation('permisos', $permisos);

                    return $first;
                })
                ->values();

            $ordenIds = [
                1 => 1, // Bienes
                2 => 2, // Movimientos
                3 => 3, // Documentos
                4 => 4, // Catálogos
                5 => 5, // Reportes
                6 => 6, // Control De Inventario
                7 => 7, // Seguridad
                8 => 8, // Config
            ];

            $menu = $menu->sortBy(function ($pm) use ($ordenIds) {
                $id = (int) ($pm->idmodulo ?? $pm->modulo?->idmodulo ?? 0);
                return $ordenIds[$id] ?? 999;
            })->values();

            $view->with('sidebarMenu', $menu);
        });
    }
}
