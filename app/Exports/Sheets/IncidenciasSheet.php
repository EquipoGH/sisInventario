<?php

namespace App\Exports\Sheets;

use App\Exports\InventarioMultiExport;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;

class IncidenciasSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    protected InventarioMultiExport $ctx;

    public function __construct(InventarioMultiExport $ctx)
    {
        $this->ctx = $ctx;
    }

    public function array(): array { return []; }
    public function title(): string { return 'Registro de Incidencias'; }
    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 18, 'C' => 22, 'D' => 20, 'E' => 34, 'F' => 26, 'G' => 48, 'H' => 40, 'I' => 16];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $inventario = $this->ctx->inventario;
                $inst       = $this->ctx->settings['nombre_institucion'] ?? 'INSTITUCIÓN';
                $lastCol    = 'I';

                $incidencias = $inventario->incidencias;
                $total      = $incidencias->count();
                $resueltas  = $incidencias->where('estado', 'revisado')->count();
                $pendientes = $total - $resueltas;

                // ── FILA 1 ──
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', strtoupper($inst));
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4A148C']],
                ]);

                // ── FILA 2 ──
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'ANEXO 03 — REGISTRO DE INCIDENCIAS Y OBSERVACIONES');
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF6A1B9A']],
                ]);

                // ── FILA 3 ──
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Inventario: ' . $inventario->codigoinventario . '    |    Generado: ' . now()->format('d/m/Y H:i'));
                $sheet->getRowDimension(3)->setRowHeight(16);
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF444444']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFF3E5F5']],
                ]);

                // ── FILA 4: espacio ──
                $sheet->getRowDimension(4)->setRowHeight(8);

                // ── FILAS 5-6: KPIs ──
                $kpis = [
                    ['A5:C5', 'A6:C6', 'TOTAL INCIDENCIAS', $total, 'FF4A148C'],
                    ['D5:F5', 'D6:F6', 'RESUELTAS', $resueltas, 'FF1B5E20'],
                    ['G5:I5', 'G6:I6', 'PENDIENTES', $pendientes, 'FFE65100'],
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

                // ── FILA 7: espacio ──
                $sheet->getRowDimension(7)->setRowHeight(8);

                // ── FILA 8: Encabezados ──
                foreach (['A8' => 'N°', 'B8' => 'FECHA REPORTE', 'C8' => 'TIPO HALLAZGO', 'D8' => 'CÓD. PATRIMONIAL', 'E8' => 'BIEN', 'F8' => 'ÁREA', 'G8' => 'OBSERVACIÓN / DETALLE', 'H8' => 'RESOLUCIÓN', 'I8' => 'ESTADO'] as $cell => $label) {
                    $sheet->setCellValue($cell, $label);
                }
                $sheet->getStyle("A8:{$lastCol}8")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF4A148C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCE93D8']]],
                ]);                // ── FILAS 9+: Datos ──
                $row = 9;
                if ($total === 0) {
                    $sheet->mergeCells("A9:I9");
                    $sheet->setCellValue("A9", "No se registraron incidencias o reportes de anomalías en este proceso de inventario.");
                    $sheet->getStyle("A9:I9")->applyFromArray([
                        'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF555555']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension(9)->setRowHeight(24);
                    $row++;
                } else {
                    foreach ($incidencias as $n => $inc) {
                        $sheet->setCellValue("A{$row}", $n + 1);
                        $sheet->setCellValue("B{$row}", $inc->fecha_registro ? $inc->fecha_registro->format('d/m/Y H:i') : '-');
                        $sheet->setCellValue("C{$row}", strtoupper(str_replace('_', ' ', $inc->tipo_incidencia ?? '-')));
                        $sheet->setCellValue("D{$row}", "'" . ($inc->bien->codigo_patrimonial ?? '-'));
                        $sheet->setCellValue("E{$row}", $inc->bien->denominacion_bien ?? '-');
                        $sheet->setCellValue("F{$row}", $inc->area->nombre_area ?? '-');
                        $sheet->setCellValue("G{$row}", $inc->observacion ?? '-');
                        $sheet->setCellValue("H{$row}", $inc->resolucion ?? 'Sin resolución registrada');
                        $resuelta = strtolower($inc->estado ?? '') === 'revisado';
                        $sheet->setCellValue("I{$row}", $resuelta ? 'RESUELTA ✓' : 'PENDIENTE ⚠');

                        $fill = ($row % 2 === 0) ? 'FFF9F0FF' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'font'      => ['size' => 9],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fill]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCE93D8']]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        ]);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("I{$row}")->applyFromArray($resuelta
                            ? ['font' => ['bold' => true, 'color' => ['argb' => 'FF1B5E20']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F5E9']]]
                            : ['font' => ['bold' => true, 'color' => ['argb' => 'FFE65100']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFF3E0']]]);
                        $sheet->getRowDimension($row)->setRowHeight(22);
                        $row++;
                    }
                }

                // ── Totales ──
                $sheet->mergeCells("A{$row}:H{$row}");
                $sheet->setCellValue("A{$row}", 'TOTAL INCIDENCIAS:');
                $sheet->setCellValue("I{$row}", $total);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $total === 0 ? 'FF2E7D32' : 'FF4A148C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => $total === 0 ? 'FF1B5E20' : 'FF38006B']]],
                ]);
                $sheet->getStyle("I{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(22);

                $sheet->freezePane('A9');
                $sheet->setAutoFilter("A8:{$lastCol}8");
            }
        ];
    }
}
