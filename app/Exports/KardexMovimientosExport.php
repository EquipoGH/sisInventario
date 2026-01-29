<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Events\AfterSheet;

use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class KardexMovimientosExport implements FromQuery, WithHeadings, ShouldAutoSize, WithEvents
{
    public function __construct(private array $filters = [])
    {
    }

    public function query()
    {
        $r = (object) $this->filters;

        $q = DB::table('movimiento as m')
            ->join('bien as b', 'b.id_bien', '=', 'm.idbien')
            ->join('tipo_bien as tb', 'tb.id_tipo_bien', '=', 'b.id_tipobien')
            ->join('tipo_mvto as tm', 'tm.id_tipo_mvto', '=', 'm.tipo_mvto')
            ->leftJoin('ubicacion as u', 'u.id_ubicacion', '=', 'm.idubicacion')
            ->leftJoin('area as a', 'a.id_area', '=', 'u.idarea')
            ->leftJoin('documento_sustento as d', 'd.id_documento', '=', 'm.documento_sustentatorio')
            ->leftJoin('estado_bien as eb', 'eb.id_estado', '=', 'm.id_estado_conservacion_bien')
            ->select([
                'm.fecha_mvto',
                'tm.tipo_mvto',
                'b.codigo_patrimonial',
                'b.denominacion_bien',
                'tb.nombre_tipo',
                DB::raw("COALESCE(a.nombre_area,'-') as area"),
                DB::raw("COALESCE(u.nombre_sede,'-') as sede"),
                DB::raw("COALESCE(u.ambiente,'-') as ambiente"),
                DB::raw("COALESCE(u.piso_ubicacion,'-') as piso"),
                DB::raw("COALESCE(eb.nombre_estado,'-') as estado"),
                DB::raw("COALESCE(d.tipo_documento,'-') as tipo_doc"),
                DB::raw("COALESCE(d.numero_documento, m.\"NumDocto\", '-') as nro_doc"),
                'm.detalle_tecnico',
            ]);

        if (!empty($r->desde)) $q->whereDate('m.fecha_mvto', '>=', $r->desde);
        if (!empty($r->hasta)) $q->whereDate('m.fecha_mvto', '<=', $r->hasta);
        if (!empty($r->tipo_mvto)) $q->where('m.tipo_mvto', $r->tipo_mvto);

        return $q->orderBy('m.fecha_mvto', 'desc');
    }

    public function headings(): array
    {
        return [
            'Fecha',
            'Tipo movimiento',
            'Código patrimonial',
            'Bien',
            'Tipo bien',
            'Área',
            'Sede',
            'Ambiente',
            'Piso',
            'Estado bien',
            'Tipo doc',
            'Nro doc',
            'Detalle técnico',
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function (AfterSheet $event) {

                $sheet = $event->sheet->getDelegate();

                // Si tus columnas son 13 => A..M
                $lastColumn = 'M';
                $lastRow = $sheet->getHighestRow();

                // Encabezado tipo “plantilla” (verde, blanco, alto, centrado, wrap)
                $headerRange = "A1:{$lastColumn}1";
                $sheet->getStyle($headerRange)->applyFromArray([
                    'font' => [
                        'bold' => true,
                        'color' => ['rgb' => 'FFFFFF'],
                        'size' => 11,
                        'name' => 'Calibri',
                    ],
                    'fill' => [
                        'fillType' => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => '2E7D32'], // verde (ajusta a tu gusto)
                    ],
                    'alignment' => [
                        'horizontal' => Alignment::HORIZONTAL_CENTER,
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'FFFFFF'], // borde blanco como muchas plantillas
                        ],
                    ],
                ]);

                // Altura del encabezado (como tu imagen)
                $sheet->getRowDimension(1)->setRowHeight(45);

                // Bordes + wrap para toda la tabla
                $tableRange = "A1:{$lastColumn}{$lastRow}";
                $sheet->getStyle($tableRange)->applyFromArray([
                    'alignment' => [
                        'vertical' => Alignment::VERTICAL_CENTER,
                        'wrapText' => true,
                    ],
                    'borders' => [
                        'allBorders' => [
                            'borderStyle' => Border::BORDER_THIN,
                            'color' => ['rgb' => 'BFBFBF'],
                        ],
                    ],
                ]);

                // Zebra (filas alternadas)
                for ($row = 2; $row <= $lastRow; $row++) {
                    if ($row % 2 === 0) {
                        $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->applyFromArray([
                            'fill' => [
                                'fillType' => Fill::FILL_SOLID,
                                'startColor' => ['rgb' => 'F7F7F7'],
                            ],
                        ]);
                    }
                }

                // Filtros (los triangulitos del header)
                $sheet->setAutoFilter($headerRange);

                // Congelar encabezado
                $sheet->freezePane('A2');

                // Alineaciones tipo plantilla (ejemplo)
                $sheet->getStyle("A2:A{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Fecha
                $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Código
                $sheet->getStyle("I2:L{$lastRow}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER); // Piso/Estado/Docs

                // Ajustes finos de ancho (opcional, porque ya tienes AutoSize)
                // Si alguna columna no se “ve bien”, fuerza un ancho:
                $sheet->getColumnDimension('D')->setWidth(30); // Bien
                $sheet->getColumnDimension('M')->setWidth(40); // Detalle técnico
            },
        ];
    }
}
