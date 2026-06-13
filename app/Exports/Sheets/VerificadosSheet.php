<?php

namespace App\Exports\Sheets;

use App\Exports\InventarioMultiExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class VerificadosSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    protected InventarioMultiExport $ctx;

    public function __construct(InventarioMultiExport $ctx)
    {
        $this->ctx = $ctx;
    }

    // Devuelve array vacío — todo se escribe en AfterSheet
    public function array(): array { return []; }

    public function title(): string { return 'Bienes Verificados'; }

    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 22, 'C' => 38, 'D' => 20, 'E' => 28, 'F' => 22, 'G' => 22, 'H' => 20, 'I' => 24];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $inventario = $this->ctx->inventario;
                $stats      = $this->ctx->estadisticas;
                $inst       = $this->ctx->settings['nombre_institucion'] ?? 'INSTITUCIÓN';
                $lastCol    = 'I';

                $totalEsper = $stats['total_esperados'] ?? 0;
                $totalVerif = $stats['total_verificados'] ?? 0;
                $rate = $totalEsper > 0 ? round($totalVerif / $totalEsper * 100, 1) : 0;

                // ── FILA 1: Nombre institución ──
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', strtoupper($inst));
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1565C0']],
                ]);

                // ── FILA 2: Título ──
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'REPORTE DE INVENTARIO PATRIMONIAL — BIENES VERIFICADOS / HALLADOS');
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 11, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1976D2']],
                ]);

                // ── FILA 3: Metadata ──
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Expediente: ' . $inventario->codigoinventario . '    |    Tipo: ' . $inventario->tipoinventario . '    |    Generado: ' . now()->format('d/m/Y H:i'));
                $sheet->getRowDimension(3)->setRowHeight(16);
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF444444']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE3F2FD']],
                ]);

                // ── FILA 4: Espacio ──
                $sheet->getRowDimension(4)->setRowHeight(8);

                // ── FILAS 5-6: KPIs ──
                $kpis = [
                    ['A5:C5', 'A6:C6', 'TOTAL ESPERADOS', $totalEsper, 'FF0D47A1'],
                    ['D5:F5', 'D6:F6', 'BIENES VERIFICADOS', $totalVerif, 'FF1B5E20'],
                    ['G5:I5', 'G6:I6', 'TASA DE CUMPLIMIENTO', $rate . '%', 'FF4A148C'],
                ];
                foreach ($kpis as [$labelRange, $valueRange, $label, $value, $color]) {
                    $sheet->mergeCells($labelRange);
                    $sheet->setCellValue(explode(':', $labelRange)[0], $label);
                    $sheet->getStyle($labelRange)->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $color]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->mergeCells($valueRange);
                    $sheet->setCellValue(explode(':', $valueRange)[0], $value);
                    $sheet->getStyle($valueRange)->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 18, 'color' => ['argb' => $color]],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF8F9FA']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                }
                $sheet->getRowDimension(5)->setRowHeight(18);
                $sheet->getRowDimension(6)->setRowHeight(34);

                // ── FILA 7: Espacio ──
                $sheet->getRowDimension(7)->setRowHeight(8);

                // ── FILA 8: Encabezados de columna ──
                $headers = ['N°', 'CÓD. PATRIMONIAL', 'DENOMINACIÓN DEL BIEN', 'TIPO DE BIEN', 'ÁREA ASIGNADA', 'AMBIENTE / UBICACIÓN', 'ESTADO CONSERVACIÓN', 'FECHA VERIFICACIÓN', 'VERIFICADO POR'];
                $cols = range('A', 'I');
                foreach ($headers as $i => $h) {
                    $sheet->setCellValue($cols[$i] . '8', $h);
                }
                $sheet->getStyle("A8:{$lastCol}8")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1565C0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFBBDEFB']]],
                ]);
                $sheet->getRowDimension(8)->setRowHeight(28);

                // ── FILAS 9+: Datos ──
                $detalles = $inventario->detalles->where('estadoverificacion', 'verificado')->values();
                $row = 9;
                foreach ($detalles as $n => $det) {
                    $bien     = $det->movimiento->bien ?? null;
                    $ubicacion = $det->movimiento->ubicacion ?? null;
                    $cons     = $det->estadoConservacion->nombre_conservacion ?? '-';

                    $sheet->setCellValue("A{$row}", $n + 1);
                    $sheet->setCellValue("B{$row}", "'" . ($bien->codigo_patrimonial ?? '-'));
                    $sheet->setCellValue("C{$row}", $bien->denominacion_bien ?? '-');
                    $sheet->setCellValue("D{$row}", $bien->tipoBien->nombre_tipo ?? '-');
                    $sheet->setCellValue("E{$row}", $ubicacion->area->nombre_area ?? '-');
                    $sheet->setCellValue("F{$row}", $ubicacion->ambiente ?? '-');
                    $sheet->setCellValue("G{$row}", $cons);
                    $sheet->setCellValue("H{$row}", $det->fechaverificacion ? $det->fechaverificacion->format('d/m/Y H:i') : '-');
                    $sheet->setCellValue("I{$row}", $det->usuarioVerificador->name ?? '-');

                    $fillColor = ($row % 2 === 0) ? 'FFF8F9FA' : 'FFFFFFFF';
                    $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                        'font'      => ['size' => 9],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fillColor]],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFE0E0E0']]],
                        'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    ]);
                    $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                    $sheet->getRowDimension($row)->setRowHeight(18);

                    // Semáforo conservación
                    $consLower = strtolower($cons);
                    if (str_contains($consLower, 'malo') || str_contains($consLower, 'deteriorado')) {
                        $sheet->getStyle("G{$row}")->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFCC0000']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFEBEE']]]);
                    } elseif (str_contains($consLower, 'regular')) {
                        $sheet->getStyle("G{$row}")->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FFE65100']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF3E0']]]);
                    } elseif (str_contains($consLower, 'bueno') || str_contains($consLower, 'óptimo')) {
                        $sheet->getStyle("G{$row}")->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FF1B5E20']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F5E9']]]);
                    }
                    $row++;
                }

                // ── Fila de totales ──
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->setCellValue("A{$row}", 'TOTAL DE BIENES VERIFICADOS:');
                $sheet->setCellValue("I{$row}", $detalles->count());
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF1565C0']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF0D47A1']]],
                ]);
                $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(22);

                $sheet->freezePane('A9');
                $sheet->setAutoFilter("A8:{$lastCol}8");
            }
        ];
    }
}
