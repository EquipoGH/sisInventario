<?php

namespace App\Exports;

use Illuminate\Support\Str;
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

    public function __construct($bienes, $settings, $filtros, $reporte = 'general')
    {
        $this->bienes = $bienes;
        $this->settings = $settings;
        $this->filtros = $filtros;
        $this->reporte = $reporte ?: 'general';
    }

    public function collection()
    {
        return $this->bienes->map(function ($b, $i) {
            $lm = $b->latestMovimiento;
            $ubic = $lm?->ubicacion;
            $area = $ubic?->area;

            $ubicTxt = $ubic
                ? trim(($ubic->nombre_sede ?? '') . ' - ' . ($ubic->ambiente ?? ''))
                : null;

            return [
                'num' => $i + 1,
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien' => mb_strtoupper($b->denominacion_bien ?? ''),
                'tipo_bien' => optional($b->tipoBien)->nombre_tipo,
                'marca_bien' => $b->marca_bien,
                'modelo_bien' => $b->modelo_bien,
                'nserie_bien' => $b->nserie_bien,
                'area' => $area?->nombre_area,
                'ubicacion' => $ubicTxt,
                'fecha_registro' => optional($b->fecha_registro)->format('d/m/Y'),
            ];
        });
    }

    public function headings(): array
    {
        return ['#', 'CÓDIGO', 'DENOMINACIÓN', 'TIPO', 'MARCA', 'MODELO', 'SERIE', 'ÁREA', 'UBICACIÓN', 'FECHA'];
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
            'J' => 14,  // Fecha
        ];
    }

    public function styles(Worksheet $sheet)
    {
        // Ojo: el header real se inserta con insertHeader() (metemos 4 filas arriba)
        // Aquí solo devolvemos array vacío y estilamos con AfterSheet para no pelear con filas insertadas.
        return [];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $this->insertHeader($sheet);

                $lastCol = 'J';

                // Fila de headings queda en la fila 5 (porque insertamos 4 filas)
                $headingRow = 5;

                // Estilos headings
                $sheet->getStyle("A{$headingRow}:{$lastCol}{$headingRow}")->applyFromArray([
                    'font' => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['argb' => 'FF0070C0'],
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                    ],
                    'borders' => [
                        'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                    ],
                ]);
                $sheet->getRowDimension($headingRow)->setRowHeight(20);

                // Data range
                $dataStart = $headingRow + 1;
                $dataEnd = $headingRow + $this->bienes->count();

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    // Centrar columnas # y fecha
                    $sheet->getStyle("A{$dataStart}:A{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("J{$dataStart}:J{$dataEnd}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                // Congelar encabezado
                $sheet->freezePane("A" . ($dataStart));
            },
        ];
    }

    protected function insertHeader(Worksheet $sheet)
    {
        $lastCol = 'J';

        // Insertar 4 filas arriba
        $sheet->insertNewRowBefore(1, 4);

        $nombreInst = mb_strtoupper($this->settings['nombre_institucion'] ?? 'INSTITUCIÓN');
        $direccion = $this->settings['direccion'] ?? '';
        $ruc = $this->settings['ruc'] ?? '';
        $telefono = $this->settings['telefono'] ?? '';

        $titulo = match($this->reporte) {
            'registrados' => 'REPORTE DE BIENES - REGISTRADOS',
            'asignados' => 'REPORTE DE BIENES - ASIGNADOS',
            'bajas' => 'REPORTE DE BIENES - BAJAS',
            default => 'REPORTE DE BIENES',
        };

        $desde = $this->filtros['desde'] ?? null;
        $hasta = $this->filtros['hasta'] ?? null;

        if (!empty($desde) && !empty($hasta)) $periodo = "{$desde} a {$hasta}";
        elseif (!empty($desde)) $periodo = "Desde {$desde}";
        elseif (!empty($hasta)) $periodo = "Hasta {$hasta}";
        else $periodo = "Todas las fechas";

        // A1: nombre institución
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $nombreInst);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A2: datos
        $sheet->mergeCells("A2:{$lastCol}2");
        $linea = trim($direccion);
        $extras = [];
        if ($ruc !== '') $extras[] = "RUC: {$ruc}";
        if ($telefono !== '') $extras[] = "Tel: {$telefono}";
        if (!empty($extras)) $linea = trim($linea . ' | ' . implode(' | ', $extras), ' |');
        $sheet->setCellValue('A2', $linea);
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A3: título
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', $titulo);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A4: período + total
        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue('A4', "Período: {$periodo} | Total: " . $this->bienes->count());
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}
