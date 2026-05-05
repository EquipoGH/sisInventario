<?php

namespace App\Observers;

use App\Models\Bien;
use App\Services\MovimientoService;
use Illuminate\Support\Facades\Log;

class BienObserver
{
    protected $movimientoService;

    public function __construct(MovimientoService $movimientoService)
    {
        $this->movimientoService = $movimientoService;
    }

    /**
     * Se ejecuta DESPUÉS de crear un bien
     */
    public function created(Bien $bien)
    {
        try {
            // Registrar movimiento de tipo "REGISTRO"
            $this->movimientoService->registrarCreacion($bien);

            Log::info("Movimiento de REGISTRO creado automáticamente para bien ID: {$bien->id_bien}");
        } catch (\Exception $e) {
            Log::error("Error al registrar movimiento de creación: " . $e->getMessage());
        }
    }

    /**
     * Se ejecuta ANTES de actualizar (para capturar valores anteriores)
     */
    public function updating(Bien $bien)
    {
        // Guardar valores originales antes de actualizar
        $bien->valoresOriginales = $bien->getOriginal();
    }

    /**
     * Se ejecuta DESPUÉS de actualizar un bien
     */
    public function updated(Bien $bien)
    {
        try {
            // Detectar cambios importantes
            $cambios = $this->detectarCambiosRelevantes($bien);

            if (!empty($cambios)) {
                // Registrar movimiento automático
                $this->movimientoService->registrarActualizacion($bien, $cambios);

                Log::info("Movimiento de ACTUALIZACIÓN creado para bien ID: {$bien->id_bien}", $cambios);
            }
        } catch (\Exception $e) {
            Log::error("Error al registrar movimiento de actualización: " . $e->getMessage());
        }
    }

    /**
     * Detectar cambios relevantes que ameritan un movimiento
     */
    private function detectarCambiosRelevantes(Bien $bien)
    {
        $cambios = [];

        // Campos que generan movimiento al cambiar
        $camposRelevantes = [
            'denominacion_bien',
            'modelo_bien',
            'marca_bien',
            'color_bien',
            'nserie_bien'
        ];

        foreach ($camposRelevantes as $campo) {
            if ($bien->isDirty($campo)) {
                $cambios[$campo] = [
                    'anterior' => $bien->valoresOriginales[$campo] ?? 'N/A',
                    'nuevo' => $bien->$campo
                ];
            }
        }

        return $cambios;
    }
}
