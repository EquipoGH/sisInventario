<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithDrawings;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class BienesExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths, WithDrawings, WithEvents
{
    protected $bienes;
    protected $settings;
    protected $filtros;

    public function __construct($bienes, $settings, $filtros)
    {
        $this->bienes = $bienes;
        $this->settings = $settings;
        $this->filtros = $filtros;
    }

    public function collection()
    {
        return $this->bienes->map(function ($b, $i) {
            return [
                'num' => $i + 1,
                'qr' => '',
                'codigo_patrimonial' => $b->codigo_patrimonial,
                'denominacion_bien' => mb_strtoupper($b->denominacion_bien ?? ''),
                'tipo_bien' => optional($b->tipoBien)->nombre_tipo,
                'marca_bien' => $b->marca_bien,
                'modelo_bien' => $b->modelo_bien,
                'nserie_bien' => $b->nserie_bien,
                'numdoc' => $b->NumDoc,
                'fecha_registro' => optional($b->fecha_registro)->format('d/m/Y'),
            ];
        });
    }

    public function headings(): array
    {
        return [
            '#',
            'QR',
            'CÓDIGO',
            'DENOMINACIÓN',
            'TIPO',
            'MARCA',
            'MODELO',
            'SERIE',
            'N° DOC',
            'FECHA',
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 6,
            'B' => 10,
            'C' => 16,
            'D' => 35,
            'E' => 22,
            'F' => 14,
            'G' => 14,
            'H' => 18,
            'I' => 15,
            'J' => 12,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:J1')->applyFromArray([
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

        $sheet->getRowDimension(1)->setRowHeight(20);

        $lastRow = $this->bienes->count() + 1;
        if ($lastRow > 1) {
            $sheet->getStyle("A2:J{$lastRow}")->applyFromArray([
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
                'borders' => [
                    'allBorders' => ['borderStyle' => Border::BORDER_THIN],
                ],
            ]);

            for ($i = 2; $i <= $lastRow; $i++) {
                $sheet->getRowDimension($i)->setRowHeight(50);
            }

            $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B2:B{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("J2:J{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
        }

        return [];
    }

    public function drawings()
    {
        $drawings = [];

        foreach ($this->bienes as $index => $bien) {
            if (empty($bien->qr_code)) continue;

            $pngBinary = base64_decode($bien->qr_code);
            
            // Guardar en storage/app/public/temp (accesible y limpiable)
            $filename = "temp_qr_{$index}_" . uniqid() . ".png";
            Storage::disk('local')->put("temp/{$filename}", $pngBinary);
            $tempPath = storage_path("app/temp/{$filename}");

            $drawing = new Drawing();
            $drawing->setName("QR {$bien->codigo_patrimonial}");
            $drawing->setDescription("QR Code");
            $drawing->setPath($tempPath);
            $drawing->setWidth(45);
            $drawing->setHeight(45);
            $drawing->setCoordinates('B' . ($index + 2));
            $drawing->setOffsetX(10);
            $drawing->setOffsetY(3);

            $drawings[] = $drawing;
        }

        return $drawings;
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();
                $this->insertHeader($sheet);
            },
        ];
    }

    protected function insertHeader(Worksheet $sheet)
    {
        $sheet->insertNewRowBefore(1, 4);

        $sheet->mergeCells('A1:J1');
        $sheet->setCellValue('A1', mb_strtoupper($this->settings['nombre_institucion'] ?? 'INSTITUCIÓN'));
        $sheet->getStyle('A1')->applyFromArray([
            'font' => ['bold' => true, 'size' => 14],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A2:J2');
        $direccion = $this->settings['direccion'] ?? '';
        $ruc = $this->settings['ruc'] ?? '';
        $telefono = $this->settings['telefono'] ?? '';
        $sheet->setCellValue('A2', "{$direccion} | RUC: {$ruc} | Tel: {$telefono}");
        $sheet->getStyle('A2')->applyFromArray([
            'font' => ['size' => 9],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->mergeCells('A3:J3');
        $sheet->setCellValue('A3', 'REPORTE DE BIENES');
        $sheet->getStyle('A3')->applyFromArray([
            'font' => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $desde = $this->filtros['desde'] ?? '';
        $hasta = $this->filtros['hasta'] ?? '';
        $rango = (!empty($desde) && !empty($hasta)) ? "Desde: {$desde} hasta {$hasta}" : 'Todos';

        $sheet->mergeCells('A4:J4');
        $sheet->setCellValue('A4', "Rango: {$rango} | Total: " . $this->bienes->count());
        $sheet->getStyle('A4')->applyFromArray([
            'font' => ['size' => 9, 'italic' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        if (!empty($this->settings['logo_reportes_abs']) && file_exists($this->settings['logo_reportes_abs'])) {
            $logo = new Drawing();
            $logo->setName('Logo');
            $logo->setPath($this->settings['logo_reportes_abs']);
            $logo->setWidth(50);
            $logo->setHeight(50);
            $logo->setCoordinates('A1');
            $logo->setOffsetX(10);
            $logo->setOffsetY(5);
            $logo->setWorksheet($sheet);
        }
    }
}
