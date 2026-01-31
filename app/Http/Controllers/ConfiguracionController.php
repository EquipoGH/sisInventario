<?php

namespace App\Http\Controllers;

use App\Models\ConfiguracionSistema;
use Illuminate\Http\Request;

class ConfiguracionController extends Controller
{
    /**
     * Mostrar panel de configuración
     */
    public function index()
        {
            $configuraciones = ConfiguracionSistema::orderBy('grupo')
                                                ->orderBy('descripcion')
                                                ->get()
                                                ->groupBy('grupo');

            // 🔥 AGREGAR ESTA LÍNEA
            $colores = ConfiguracionSistema::obtenerColores();

            return view('configuracion.index', compact('configuraciones', 'colores'));
        }


    /**
     * Actualizar configuraciones
     */
    public function actualizar(Request $request)
    {
        try {
            foreach ($request->except('_token') as $clave => $valor) {
                ConfiguracionSistema::actualizar($clave, $valor);
            }

            // Limpiar cache completo
            ConfiguracionSistema::limpiarCache();

            return redirect()->back()->with('success', '✅ Configuración actualizada exitosamente');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', '❌ Error al actualizar: ' . $e->getMessage());
        }
    }
}
