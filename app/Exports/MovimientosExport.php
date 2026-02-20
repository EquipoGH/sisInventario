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
     * @param \Illuminate\Support\Collection|array $rows  Colección de filas (stdClass o array) desde DB::table.
     */
    public function __construct($rows, array $settings = [], array $filtros = [], string $reporte = 'movimientos_por_fecha')
    {
        $this->rows = $rows instanceof Collection ? $rows : collect($rows);
        $this->settings = $settings;
        $this->filtros = $filtros;
        $this->reporte = $reporte ?: 'movimientos_por_fecha';
    }

    public function collection()
    {
        return $this->rows->values()->map(function ($r, $i) {
            $fecha = null;
            if (!empty($r->fecha_mvto)) {
                try { $fecha = Carbon::parse($r->fecha_mvto)->format('d/m/Y'); }
                catch (\Throwable $e) { $fecha = (string) $r->fecha_mvto; }
            }

            $ubicacion = trim(($r->nombre_sede ?? '') . ' - ' . ($r->ambiente ?? ''));
            if ($ubicacion === '-' || $ubicacion === '') $ubicacion = null;

            $documento = trim(($r->tipodocumento ?? '') . ' ' . ($r->numerodocumento ?? ''));
            if ($documento === '') $documento = null;

            return [
                $i + 1,
                $r->codigopatrimonial ?? null,
                mb_strtoupper($r->denominacionbien ?? ''),
                $r->tipo_bien ?? null,
                $fecha,
                $r->tipo_mov ?? null,      // tipo_mvto ya viene como alias tipo_mov [file:2]
                $r->area ?? null,
                $ubicacion,
            ];
        });
    }

    public function headings(): array
    {
        return ['#', 'CÓDIGO', 'DENOMINACIÓN', 'TIPO BIEN', 'FECHA MOV.', 'MOVIMIENTO', 'ÁREA', 'UBICACIÓN'];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 18,
            'C' => 42,
            'D' => 18,
            'E' => 14,
            'F' => 18,
            'G' => 22,
            'H' => 34,
            'I' => 22,
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

                $lastCol = 'I';
                $headingRow = 5;

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

                $dataStart = $headingRow + 1;
                $dataEnd = $headingRow + $this->rows->count();

                if ($dataEnd >= $dataStart) {
                    $sheet->getStyle("A{$dataStart}:{$lastCol}{$dataEnd}")->applyFromArray([
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders' => [
                            'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                        ],
                    ]);

                    $sheet->getStyle("A{$dataStart}:A{$dataEnd}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getStyle("E{$dataStart}:E{$dataEnd}")
                        ->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                }

                $sheet->freezePane("A" . ($dataStart));
            },
        ];
    }

    protected function insertHeader(Worksheet $sheet): void
    {
        $lastCol = 'I';
        $sheet->insertNewRowBefore(1, 4);

        $nombreInst = mb_strtoupper($this->settings['nombre_institucion'] ?? 'INSTITUCIÓN');
        $direccion = $this->settings['direccion'] ?? '';
        $ruc = $this->settings['ruc'] ?? '';
        $telefono = $this->settings['telefono'] ?? '';

        $titulo = 'REPORTE DE MOVIMIENTOS (POR BIEN)';

        $desde = $this->filtros['desde'] ?? null;
        $hasta = $this->filtros['hasta'] ?? null;

        if (!empty($desde) && !empty($hasta)) $periodo = "{$desde} a {$hasta}";
        elseif (!empty($desde)) $periodo = "Desde {$desde}";
        elseif (!empty($hasta)) $periodo = "Hasta {$hasta}";
        else $periodo = "Todas las fechas";

        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', $nombreInst);
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

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

        $sheet->mergeCells("A3:{$lastCol}3");
        $sheet->setCellValue('A3', $titulo);
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells("A4:{$lastCol}4");
        $sheet->setCellValue('A4', "Período: {$periodo} | Total: " . $this->rows->count());
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
    }
}
