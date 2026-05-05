<?php

use App\Models\ConfiguracionSistema;

if (!function_exists('config_sistema')) {
    /**
     * Obtener configuración del sistema desde BD
     */
    function config_sistema($clave, $default = null)
    {
        try {
            return ConfiguracionSistema::obtener($clave, $default);
        } catch (\Exception $e) {
            return $default;
        }
    }
}
