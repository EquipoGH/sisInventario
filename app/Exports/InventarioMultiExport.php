<?php

namespace App\Exports;

use App\Models\Inventario;
use App\Models\SystemSetting;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use App\Exports\Sheets\VerificadosSheet;
use App\Exports\Sheets\FaltantesSheet;
use App\Exports\Sheets\SobrantesSheet;
use App\Exports\Sheets\IncidenciasSheet;

class InventarioMultiExport implements WithMultipleSheets
{
    use Exportable;

    public Inventario $inventario;
    public array      $estadisticas;
    public array      $settings;

    public function __construct(Inventario $inventario)
    {
        $inventario->load([
            'responsablePersona',
            'detalles.movimiento.bien.tipoBien',
            'detalles.movimiento.ubicacion.area',
            'detalles.estadoConservacion',
            'detalles.ubicacionDetectada.area',
            'detalles.usuarioVerificador',
            'incidencias.bien',
            'incidencias.area',
        ]);

        $this->inventario   = $inventario;
        $this->estadisticas = $inventario->getEstadisticasConciliacion();
        $this->settings     = SystemSetting::pluck('value', 'key')->toArray();
    }

    public function sheets(): array
    {
        $sheets = [];

        // Hoja 1: Verificados
        $sheets[] = new VerificadosSheet($this);

        // Hoja 2: Faltantes
        $faltantesIds = $this->estadisticas['faltantes_ids'] ?? [];
        $sheets[] = new FaltantesSheet($this, $faltantesIds);

        // Hoja 3: Sobrantes
        $sobrantesIds = $this->estadisticas['sobrantes_ids'] ?? [];
        $sheets[] = new SobrantesSheet($this, $sobrantesIds);

        // Hoja 4: Incidencias
        $sheets[] = new IncidenciasSheet($this);

        return $sheets;
    }
}
