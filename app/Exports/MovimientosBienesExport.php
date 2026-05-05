<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class MovimientosBienesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithEvents
{
    /** @var \Illuminate\Support\Collection */
    protected $rows;

    protected array $settings;
    protected array $filtros;
    protected string $reporte;

    /**
     * @param \Illuminate\Support\Collection|array $rows  Colección de stdClass desde DB::table (baseQuery).
     */
    public function __construct($rows, array $settings = [], array $filtros = [], string $reporte = 'movimientos_por_fecha')
    {
        $this->rows     = $rows instanceof Collection ? $rows : collect($rows);
        $this->settings = $settings;
        $this->filtros  = $filtros;
        $this->reporte  = $reporte ?: 'movimientos_por_fecha';
    }

    public function collection()
    {
        return $this->rows->values()->map(function ($r, $i) {
            // ✅ Aliases correctos según el SELECT en baseQuery():
            //    b.codigo_patrimonial, b.denominacion_bien, tb.nombre_tipo as tipo_bien,
            //    m.fecha_mvto, tm.tipo_mvto as tipo_mov, a.nombre_area as area,
            //    u.nombre_sede, u.ambiente

            $fecha = null;
            if (!empty($r->fecha_mvto)) {
                try { $fecha = Carbon::parse($r->fecha_mvto)->format('d/m/Y'); }
                catch (\Throwable $e) { $fecha = (string) $r->fecha_mvto; }
            }

            $ubicacion = trim($r->ambiente ?? '');
            if ($ubicacion === '') $ubicacion = null;

            return [
                $i + 1,
                $r->codigo_patrimonial ?? null,          // ✅ correcto (no codigopatrimonial)
                mb_strtoupper($r->denominacion_bien ?? ''), // ✅ correcto (no denominacionbien)
                $r->tipo_bien  ?? null,                  // alias: tb.nombre_tipo as tipo_bien
                $fecha,
                $r->tipo_mov   ?? null,                  // alias: tm.tipo_mvto as tipo_mov
                $r->area       ?? null,                  // alias: a.nombre_area as area
                $ubicacion,
            ];
        });
    }

    public function headings(): array
    {
        // ✅ 8 headings = 8 columnas (A-H), consistente con collection()
        return ['#', 'CÓDIGO', 'DENOMINACIÓN', 'TIPO BIEN', 'FECHA MOV.', 'MOVIMIENTO', 'ÁREA', 'UBICACIÓN'];
    }

    public function columnWidths(): array
    {
        // ✅ 8 columnas: A-H (antes tenía I de más)
        return [
            'A' => 6,
            'B' => 18,
            'C' => 42,
            'D' => 18,
            'E' => 14,
            'F' => 18,
            'G' => 22,
            'H' => 34,
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

                $lastCol    = 'H'; // ✅ 8 columnas (A-H)
                $headingRow = 5;

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

                $dataStart = $headingRow + 1;
                $dataEnd   = $headingRow + $this->rows->count();

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders'   => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    // Centrar # y fecha
                    $sheet->getStyle("A{$dataStart}:A{$dataEnd}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$dataStart}:E{$dataEnd}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

                    // Alternado de filas
                    for ($row = $dataStart; $row <= $dataEnd; $row++) {
                        if (($row - $dataStart) % 2 === 1) {
                            $sheet->getStyle("A{$row}:{$lastCol}{$row}")->getFill()
                                ->setFillType(Fill::FILL_SOLID)
                                ->getStartColor()->setARGB('FFF2F2F2');
                        }
                    }
                }

                $sheet->freezePane("A{$dataStart}");
            },
        ];
    }

    protected function insertHeader(Worksheet $sheet): void
    {
        $lastCol = 'H'; // ✅ 8 columnas

        $sheet->insertNewRowBefore(1, 4);

        $nombreInst = mb_strtoupper($this->settings['nombre_institucion'] ?? 'INSTITUCIÓN');
        $direccion  = $this->settings['direccion'] ?? '';
        $ruc        = $this->settings['ruc']       ?? '';
        $telefono   = $this->settings['telefono']  ?? '';

        $titulo = 'REPORTE DE MOVIMIENTOS (POR BIEN)';

        // ✅ Filtros reales: desde, hasta, tipo_mvto, area_id, ubicacion_id, q
        $desde = $this->filtros['desde'] ?? null;
        $hasta = $this->filtros['hasta'] ?? null;

        if (!empty($desde) && !empty($hasta)) $periodo = "{$desde} a {$hasta}";
        elseif (!empty($desde)) $periodo = "Desde {$desde}";
        elseif (!empty($hasta)) $periodo = "Hasta {$hasta}";
        else $periodo = "Todas las fechas";

        $filtroPartes = [];
        if (!empty($this->filtros['tipo_mvto_nombre'])) $filtroPartes[] = "Tipo mov.: {$this->filtros['tipo_mvto_nombre']}";
        if (!empty($this->filtros['area_nombre']))      $filtroPartes[] = "Área: {$this->filtros['area_nombre']}";
        if (!empty($this->filtros['ubicacion_nombre'])) $filtroPartes[] = "Ubic.: {$this->filtros['ubicacion_nombre']}";
        if (!empty($this->filtros['q']))                $filtroPartes[] = "Búsqueda: \"{$this->filtros['q']}\"";

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

        // A3: título
        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', $titulo);
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // A4: período + filtros + total + fecha generación
        $resumen = "Período: {$periodo}";
        if (!empty($filtroPartes)) $resumen .= ' | ' . implode(' | ', $filtroPartes);
        $resumen .= ' | Total: ' . $this->rows->count() . ' | Generado: ' . now()->format('d/m/Y H:i');

        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue('A4', $resumen);
        $sheet->getStyle('A4')->applyFromArray([
            'font'      => ['size' => 9, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}
