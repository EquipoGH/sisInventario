<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BienesExport implements FromView, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(
        public $bienes,
        public array $settings = [],
        public array $filtros = []
    ) {}

    public function view(): View
    {
        return view('reportes.bienes.excel', [
            'bienes' => $this->bienes,
            'settings' => $this->settings,
            'filtros' => $this->filtros,
        ]);
    }

    public function title(): string
    {
        return 'Reporte Bienes';
    }

    public function styles(Worksheet $sheet)
    {
        $lastRow = $this->bienes->count() + 6; // ajusta según filas header
        $headerRow = 6; // fila donde empiezan headers

        // Título principal (fila 1)
        $sheet->mergeCells('A1:I1');
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Subtítulo (fila 2)
        $sheet->mergeCells('A2:I2');
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Info adicional (filas 3-4)
        $sheet->mergeCells('A3:I3');
        $sheet->mergeCells('A4:I4');

        // Headers (fila 6) - Verde con blanco
        $sheet->getStyle("A{$headerRow}:I{$headerRow}")->applyFromArray([
            'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 11],
            'fill' => [
                'fillType' => Fill::FILL_SOLID,
                'startColor' => ['rgb' => '28a745'], // Verde Bootstrap
            ],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => '000000']],
            ],
        ]);

        // Datos (desde fila 7 hasta última)
        if ($lastRow > $headerRow) {
            $dataRange = "A" . ($headerRow + 1) . ":I{$lastRow}";
            $sheet->getStyle($dataRange)->applyFromArray([
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']],
                ],
            ]);

            // Centrar columnas específicas
            $sheet->getStyle("A" . ($headerRow + 1) . ":A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // #
            $sheet->getStyle("I" . ($headerRow + 1) . ":I{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // FECHA
        }

        // Filas alternadas (zebra striping)
        for ($row = $headerRow + 1; $row <= $lastRow; $row++) {
            if (($row - $headerRow) % 2 == 0) {
                $sheet->getStyle("A{$row}:I{$row}")->applyFromArray([
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F8F9FA'], // Gris claro
                    ],
                ]);
            }
        }

        // Ajustar altura de filas
        $sheet->getRowDimension($headerRow)->setRowHeight(20);

        // Congelar paneles (freeze)
        $sheet->freezePane('A7'); // Congela hasta fila 6

        // Autofiltro
        $sheet->setAutoFilter("A{$headerRow}:I{$headerRow}");

        return [];
    }
}
