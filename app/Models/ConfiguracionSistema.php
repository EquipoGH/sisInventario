<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class ConfiguracionSistema extends Model
{
    protected $table = 'configuracion_sistema';
    protected $fillable = ['clave', 'valor', 'tipo', 'grupo', 'descripcion'];

    /**
     * Obtener configuración por clave (con cache de 1 hora)
     */
    public static function obtener($clave, $default = null)
    {
        return Cache::remember("config_{$clave}", 3600, function() use ($clave, $default) {
            $config = self::where('clave', $clave)->first();
            return $config ? $config->valor : $default;
        });
    }

    /**
     * Obtener todas las configuraciones de un grupo
     */
    public static function obtenerGrupo($grupo)
    {
        return Cache::remember("config_grupo_{$grupo}", 3600, function() use ($grupo) {
            return self::where('grupo', $grupo)->pluck('valor', 'clave')->toArray();
        });
    }

    /**
 * Obtener TODOS los colores del sistema (para inyectar en vistas)
 */
public static function obtenerColores()
{
    return Cache::remember('colores_sistema', 3600, function() {
        return self::where('tipo', 'color')
                   ->pluck('valor', 'clave')
                   ->toArray();
    });
}


    /**
     * Actualizar configuración y limpiar cache
     */
    public static function actualizar($clave, $valor)
{
    $config = self::where('clave', $clave)->first();
    if ($config) {
        $config->valor = $valor;
        $config->save();
        Cache::forget("config_{$clave}");
        Cache::forget("config_grupo_{$config->grupo}");
        Cache::forget('colores_sistema'); // 🔥 AGREGAR ESTA LÍNEA
        return true;
    }
    return false;
}


    /**
     * Limpiar todo el cache de configuraciones
     */
    public static function limpiarCache()
    {
        Cache::flush();
    }
}
