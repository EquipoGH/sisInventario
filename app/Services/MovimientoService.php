<?php

namespace App\Services;

use App\Models\Movimiento;
use App\Models\TipoMvto;
use App\Models\EstadoBien;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class MovimientoService
{
    /**
     * Registrar movimiento automático al crear un bien
     */
    public function registrarCreacion($bien)
    {
        try {
            $tipoRegistro = TipoMvto::where('tipo_mvto', 'ILIKE', 'REGISTRO')->first();

            if (!$tipoRegistro) {
                throw new \Exception('Tipo de movimiento "REGISTRO" no encontrado en la BD');
            }

            // ⭐ Obtener estado "BUENO"
            $estadoBueno = EstadoBien::where('nombre_estado', 'ILIKE', 'BUENO')->first();

            if (!$estadoBueno) {
                Log::warning('Estado "BUENO" no encontrado, el movimiento se creará sin estado');
            }

            return Movimiento::create([
                'idbien' => $bien->id_bien,
                'tipo_mvto' => $tipoRegistro->id_tipo_mvto,
                'fecha_mvto' => now(),
                'detalle_tecnico' => 'Registro inicial del bien: ' . strtoupper($bien->denominacion_bien),
                'documento_sustentatorio' => $bien->id_documento ?? null,
                'NumDocto' => $bien->NumDoc ?? null,  // ⭐ NUEVO
                'idubicacion' => $bien->idubicacion ?? null,
                'id_estado_conservacion_bien' => $estadoBueno ? $estadoBueno->id_estado : null,  // ⭐ NUEVO
                'idusuario' => Auth::id()
            ]);

        } catch (\Exception $e) {
            Log::error('Error en registrarCreacion: ' . $e->getMessage());
            throw $e;
        }
    }

    /**
     * Registrar movimiento automático al actualizar un bien
     */
    public function registrarActualizacion($bien, $cambios)
    {
        // Detectar tipo de movimiento según los cambios
        $tipoMovimiento = $this->detectarTipoMovimiento($cambios);

        if (!$tipoMovimiento) {
            // Si no hay cambios relevantes, no registrar movimiento
            return null;
        }

        $detalle = $this->generarDetalleMovimiento($cambios, $bien);

        return Movimiento::create([
            'idbien' => $bien->id_bien,
            'tipo_mvto' => $tipoMovimiento->id_tipo_mvto,
            'fecha_mvto' => now(),
            'detalle_tecnico' => $detalle,
            'documento_sustentatorio' => $bien->id_documento ?? null,
            'NumDocto' => $bien->NumDoc ?? null,  // ⭐ NUEVO
            'idubicacion' => $bien->idubicacion ?? null,
            'id_estado_conservacion_bien' => $bien->id_estado_conservacion_bien ?? null,
            'idusuario' => Auth::id()
        ]);
    }

    /**
     * Detectar tipo de movimiento según cambios
     */
    private function detectarTipoMovimiento($cambios)
    {
        // Si cambió la ubicación
        if (isset($cambios['idubicacion'])) {
            return TipoMvto::where('tipo_mvto', 'ILIKE', '%TRASLADO%')
                           ->orWhere('tipo_mvto', 'ILIKE', '%UBICACION%')
                           ->first();
        }

        // Si cambió el estado de conservación
        if (isset($cambios['id_estado_conservacion_bien'])) {
            return TipoMvto::where('tipo_mvto', 'ILIKE', '%MANTENIMIENTO%')
                           ->orWhere('tipo_mvto', 'ILIKE', '%ESTADO%')
                           ->first();
        }

        // Si cambió el área
        if (isset($cambios['idarea'])) {
            return TipoMvto::where('tipo_mvto', 'ILIKE', '%ASIGNACION%')
                           ->orWhere('tipo_mvto', 'ILIKE', '%AREA%')
                           ->first();
        }

        // Movimiento genérico de actualización
        return TipoMvto::where('tipo_mvto', 'ILIKE', '%ACTUALIZACION%')
                       ->orWhere('tipo_mvto', 'ILIKE', '%MODIFICACION%')
                       ->first();
    }

    /**
     * Generar detalle del movimiento
     */
    private function generarDetalleMovimiento($cambios, $bien)
    {
        $detalles = [];

        foreach ($cambios as $campo => $valor) {
            $nombreCampo = $this->obtenerNombreCampo($campo);
            $detalles[] = "{$nombreCampo}: {$valor['anterior']} → {$valor['nuevo']}";
        }

        $texto = 'Actualización de ' . strtoupper($bien->denominacion_bien) . ': ' . implode(', ', $detalles);

        // Truncar si es muy largo
        return strlen($texto) > 200 ? substr($texto, 0, 197) . '...' : $texto;
    }

    /**
     * Obtener nombre amigable del campo
     */
    private function obtenerNombreCampo($campo)
    {
        $nombres = [
            'idubicacion' => 'Ubicación',
            'id_estado_conservacion_bien' => 'Estado',
            'idarea' => 'Área',
            'denominacion_bien' => 'Denominación',
            'modelo_bien' => 'Modelo',
            'marca_bien' => 'Marca',
            'color_bien' => 'Color',
            'NumDoc' => 'Número de Documento',
        ];

        return $nombres[$campo] ?? ucfirst($campo);
    }
}
