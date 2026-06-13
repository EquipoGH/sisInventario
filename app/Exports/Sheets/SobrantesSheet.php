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

class SobrantesSheet implements FromArray, WithTitle, WithColumnWidths, WithEvents
{
    protected InventarioMultiExport $ctx;
    protected array $sobrantesIds;

    public function __construct(InventarioMultiExport $ctx, array $sobrantesIds)
    {
        $this->ctx = $ctx;
        $this->sobrantesIds = $sobrantesIds;
    }

    public function array(): array { return []; }
    public function title(): string { return 'Bienes Sobrantes'; }
    public function columnWidths(): array
    {
        return ['A' => 6, 'B' => 22, 'C' => 38, 'D' => 20, 'E' => 28, 'F' => 22, 'G' => 28, 'H' => 22];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet      = $event->sheet->getDelegate();
                $inventario = $this->ctx->inventario;
                $inst       = $this->ctx->settings['nombre_institucion'] ?? 'INSTITUCIÓN';
                $lastCol    = 'H';

                $detalles = $inventario->detalles->filter(function ($det) {
                    return $det->movimiento && in_array($det->movimiento->idbien, $this->sobrantesIds);
                })->values();
                $total = $detalles->count();

                // ── FILA 1 ──
                $sheet->mergeCells("A1:{$lastCol}1");
                $sheet->setCellValue('A1', strtoupper($inst));
                $sheet->getRowDimension(1)->setRowHeight(28);
                $sheet->getStyle("A1:{$lastCol}1")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 14, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF00695C']],
                ]);

                // ── FILA 2 ──
                $sheet->mergeCells("A2:{$lastCol}2");
                $sheet->setCellValue('A2', 'ANEXO 02 — RELACIÓN DE BIENES SOBRANTES (UBICACIÓN INCORRECTA)');
                $sheet->getRowDimension(2)->setRowHeight(22);
                $sheet->getStyle("A2:{$lastCol}2")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 12, 'color' => ['argb' => 'FFFFFFFF']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FF00796B']],
                ]);

                // ── FILA 3 ──
                $sheet->mergeCells("A3:{$lastCol}3");
                $sheet->setCellValue('A3', 'Inventario: ' . $inventario->codigoinventario . '    |    Generado: ' . now()->format('d/m/Y H:i'));
                $sheet->getRowDimension(3)->setRowHeight(16);
                $sheet->getStyle("A3:{$lastCol}3")->applyFromArray([
                    'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF444444']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0F2F1']],
                ]);

                // ── FILA 4: espacio ──
                $sheet->getRowDimension(4)->setRowHeight(8);

                // ── FILA 5: Aviso ──
                $sheet->mergeCells("A5:{$lastCol}5");
                $sheet->getRowDimension(5)->setRowHeight(30);
                if ($total === 0) {
                    $sheet->setCellValue('A5', '   ✓  SIN SOBRANTES: Todos los bienes verificados se ubicaron en sus respectivas áreas y ubicaciones registradas en el sistema.');
                    $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF1B5E20']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE8F5E9']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF2E7D32']]],
                    ]);
                } else {
                    $sheet->setCellValue('A5', '   🔄  TOTAL: ' . $total . ' bienes sobrantes  —  Hallados en área distinta a la registrada en el sistema. Requieren regularización de ubicación patrimonial.');
                    $sheet->getStyle("A5:{$lastCol}5")->applyFromArray([
                        'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FF004D40']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFE0F2F1']],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_LEFT, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => 'FF00897B']]],
                    ]);
                }

                // ── FILA 6: espacio ──
                $sheet->getRowDimension(6)->setRowHeight(8);

                // ── FILA 7: Encabezados ──
                foreach (['A7' => 'N°', 'B7' => 'CÓD. PATRIMONIAL', 'C7' => 'DENOMINACIÓN DEL BIEN', 'D7' => 'TIPO DE BIEN', 'E7' => 'ÁREA ORIGINAL (SISTEMA)', 'F7' => 'UBICACIÓN ORIGINAL', 'G7' => 'ÁREA FÍSICA DETECTADA', 'H7' => 'UBICACIÓN DETECTADA'] as $cell => $label) {
                    $sheet->setCellValue($cell, $label);
                }
                $sheet->getStyle("A7:{$lastCol}7")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 9, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $total === 0 ? 'FF2E7D32' : 'FF00695C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => $total === 0 ? 'FFC8E6C9' : 'FFB2DFDB']]],
                ]);
                $sheet->getRowDimension(7)->setRowHeight(26);

                // ── FILAS 8+: Datos ──
                $row = 8;
                if ($total === 0) {
                    $sheet->mergeCells("A8:H8");
                    $sheet->setCellValue("A8", "No se registraron bienes sobrantes en este proceso de inventario.");
                    $sheet->getStyle("A8:H8")->applyFromArray([
                        'font'      => ['italic' => true, 'size' => 9, 'color' => ['argb' => 'FF555555']],
                        'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFFFFFFF']],
                        'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFCCCCCC']]],
                        'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
                    ]);
                    $sheet->getRowDimension(8)->setRowHeight(24);
                    $row++;
                } else {
                    foreach ($detalles as $n => $det) {
                        $bien     = $det->movimiento->bien ?? null;
                        $areaOrig = $det->movimiento->ubicacion->area->nombre_area ?? 'SIN ASIGNAR';
                        $ambOrig  = $det->movimiento->ubicacion->ambiente ?? '-';
                        $areaFis  = $det->ubicacionDetectada->area->nombre_area ?? 'NO ESPECIFICADA';
                        $ambFis   = $det->ubicacionDetectada->ambiente ?? '-';

                        $sheet->setCellValue("A{$row}", $n + 1);
                        $sheet->setCellValue("B{$row}", "'" . ($bien->codigo_patrimonial ?? '-'));
                        $sheet->setCellValue("C{$row}", $bien->denominacion_bien ?? '-');
                        $sheet->setCellValue("D{$row}", $bien->tipoBien->nombre_tipo ?? '-');
                        $sheet->setCellValue("E{$row}", $areaOrig);
                        $sheet->setCellValue("F{$row}", $ambOrig);
                        $sheet->setCellValue("G{$row}", $areaFis);
                        $sheet->setCellValue("H{$row}", $ambFis);

                        $fill = ($row % 2 === 0) ? 'FFF1F8F7' : 'FFFFFFFF';
                        $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                            'font'      => ['size' => 9],
                            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $fill]],
                            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['argb' => 'FFB2DFDB']]],
                            'alignment' => ['vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
                        ]);
                        $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                        $sheet->getStyle("G{$row}:H{$row}")->applyFromArray(['font' => ['bold' => true, 'color' => ['argb' => 'FF004D40']], 'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFC8E6C9']]]);
                        $sheet->getRowDimension($row)->setRowHeight(18);
                        $row++;
                    }
                }

                // ── Totales ──
                $sheet->mergeCells("A{$row}:G{$row}");
                $sheet->setCellValue("A{$row}", 'TOTAL BIENES SOBRANTES:');
                $sheet->setCellValue("H{$row}", $total);
                $sheet->getStyle("A{$row}:{$lastCol}{$row}")->applyFromArray([
                    'font'      => ['bold' => true, 'size' => 10, 'color' => ['argb' => 'FFFFFFFF']],
                    'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => $total === 0 ? 'FF2E7D32' : 'FF00695C']],
                    'alignment' => ['horizontal' => Alignment::HORIZONTAL_RIGHT, 'vertical' => Alignment::VERTICAL_CENTER],
                    'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['argb' => $total === 0 ? 'FF1B5E20' : 'FF004D40']]],
                ]);
                $sheet->getStyle("H{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
                $sheet->getRowDimension($row)->setRowHeight(22);

                $sheet->freezePane('A8');
                $sheet->setAutoFilter("A7:{$lastCol}7");
            }
        ];
    }
}
