<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\ConfiguracionSistema;

class ConfiguracionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     */
    public function boot(): void
{
    View::composer('*', function ($view) {
        try {
            // 🔥 CAMBIAR obtenerGrupo() por obtenerColores()
            $colores = ConfiguracionSistema::obtenerColores();
            $view->with('colores', $colores);
        } catch (\Exception $e) {
            // 🔥 Valores por defecto si hay error
            $view->with('colores', [
                'color_header_crear' => '#007bff',
                'color_header_editar' => '#17a2b8',
                'color_header_eliminar' => '#dc3545',
                'color_tabla_header' => '#343a40',
                'color_tabla_hover' => '#e3f2fd',
                'color_btn_primario' => '#007bff',
                'color_btn_success' => '#28a745',
                'color_btn_danger' => '#dc3545',
                'color_badge_info' => '#17a2b8',
                'color_badge_warning' => '#ffc107',
            ]);
        }
    });
}


    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }
}
