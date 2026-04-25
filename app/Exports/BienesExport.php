<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BienesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    protected $bienes;
    protected $settings;
    protected $filtros;
    protected $reporte;

    public function __construct($bienes, $settings, $filtros, $reporte = 'inventario_general')
    {
        $this->bienes   = $bienes;
        $this->settings = $settings;
        $this->filtros  = $filtros;
        $this->reporte  = $reporte ?: 'inventario_general';
    }

    public function collection()
    {
        return $this->bienes->map(function ($b, $i) {
            $lm     = $b->latestMovimiento;
            $ubic   = $lm?->ubicacion;
            $area   = $ubic?->area;

            // Estado de conservación del bien (viene del ultimo movimiento)
            $estadoCons = $lm?->estadoConservacion?->nombre_estado;

            // Usuario del sistema que registró el último movimiento
            $usuarioTxt = $lm?->usuario?->name;

            $ubicTxt = $ubic
                ? trim($ubic->ambiente ?? '')
                : null;

            return [
                'num'               => $i + 1,
                'codigo_patrimonial'=> $b->codigo_patrimonial,
                'denominacion_bien' => mb_strtoupper($b->denominacion_bien ?? ''),
                'tipo_bien'         => optional($b->tipoBien)->nombre_tipo,
                'marca_bien'        => $b->marca_bien,
                'modelo_bien'       => $b->modelo_bien,
                'nserie_bien'       => $b->nserie_bien,
                'area'              => $area?->nombre_area,
                'ubicacion'         => $ubicTxt,
                'estado_conservacion' => $estadoCons,
                'registrado_por'    => $usuarioTxt,
                'fecha_registro'    => optional($b->fecha_registro)->format('d/m/Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'CÓDIGO',
            'DENOMINACIÓN',
            'TIPO',
            'MARCA',
            'MODELO',
            'SERIE',
            'ÁREA',
            'UBICACIÓN',
            'ESTADO CONS.',
            'REGISTRADO POR',
            'FECHA REGISTRO',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,   // #
            'B' => 18,  // Código
            'C' => 40,  // Denominación
            'D' => 20,  // Tipo
            'E' => 16,  // Marca
            'F' => 16,  // Modelo
            'G' => 20,  // Serie
            'H' => 22,  // Área
            'I' => 34,  // Ubicación
            'J' => 18,  // Estado Conservación
            'K' => 24,  // Registrado por
            'L' => 14,  // Fecha Registro
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->insertHeader($sheet);

                $lastCol = 'L'; // Actualizado: ahora son 12 columnas (A-L)

                // Fila de headings queda en la fila 5 (porque insertamos 4 filas)
                $headingRow = 5;

                // Estilos headings
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0070C0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical'   => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(20);

                // Data range
                $dataStart = $headingRow + 1;
                $dataEnd   = $headingRow + $this->bienes->count();

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders'   => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    // Centrar columnas # y fecha registro
                    $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("L{$dataStart}:L{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Alternado de filas
                    for ($row = $dataStart; $row <= $dataEnd; $row++) {
                        if (($row - $dataStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFF2F2F2');
                        }
                    }
                }

                // Congelar encabezado
                $sheet->freezePane("A{$dataStart}");
            },
        ];
    }

    protected function insertHeader(Worksheet $sheet)
    {
        $lastCol = 'L';

        // Insertar 4 filas arriba
        $sheet->insertNewRowBefore(1, 4);

        $nombreInst = mb_strtoupper($this->settings['nombre_institucion'] ?? 'INSTITUCIÓN');
        $direccion  = $this->settings['direccion'] ?? '';
        $ruc        = $this->settings['ruc']       ?? '';
        $telefono   = $this->settings['telefono']  ?? '';

        // ✅ Tipos de reporte correctos (igual que el controlador)
        $titulo = match($this->reporte) {
            'inventario_area'         => 'REPORTE DE BIENES - INVENTARIO POR ÁREA Y UBICACIÓN',
            'inventario_estado_admin' => 'REPORTE DE BIENES - INVENTARIO POR ESTADO DE CONSERVACIÓN',
            'bienes_responsable'      => 'REPORTE DE BIENES - BIENES POR RESPONSABLE',
            default                   => 'REPORTE DE BIENES - INVENTARIO GENERAL',
        };

        // ✅ Filtros reales que envía el controlador
        $filtroPartes = [];
        if (!empty($this->filtros['anio']))             $filtroPartes[] = "Año: {$this->filtros['anio']}";
        if (!empty($this->filtros['area_nombre']))      $filtroPartes[] = "Área: {$this->filtros['area_nombre']}";
        if (!empty($this->filtros['ubicacion_nombre'])) $filtroPartes[] = "Ubicación: {$this->filtros['ubicacion_nombre']}";
        if (!empty($this->filtros['tipo_bien_nombre'])) $filtroPartes[] = "Tipo bien: {$this->filtros['tipo_bien_nombre']}";
        $periodo = !empty($filtroPartes) ? implode(' | ', $filtroPartes) : 'Todos los registros';

        // A1: nombre institución
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $nombreInst);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A2: dirección + RUC + teléfono
        $sheet->mergeCells("A2:{$lastCol}2");
        $linea  = trim($direccion);
        $extras = [];
        if ($ruc !== '')      $extras[] = "RUC: {$ruc}";
        if ($telefono !== '') $extras[] = "Tel: {$telefono}";
        if (!empty($extras))  $linea = trim($linea . ' | ' . implode(' | ', $extras), ' |');
        $sheet->setCellValue('A2', $linea);
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A3: título del reporte
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', $titulo);
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A4: período/filtros + total + estado
        $estadoTxt = match($this->filtros['estado'] ?? 'activos') {
            'inactivos' => 'Inactivos',
            'todos'     => 'Todos',
            default     => 'Activos',
        };
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue('A4', "Estado: {$estadoTxt} | Filtros: {$periodo} | Total: " . $this->bienes->count() . " | Fecha: " . now()->format('d/m/Y H:i'));
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}
