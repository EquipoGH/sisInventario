<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\TipoBien;
use App\Models\DocumentoSustento;
use App\Exports\BienesExport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

use Endroid\QrCode\QrCode;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\ErrorCorrectionLevel;

class ReporteBienController extends Controller
{
    private function buildQuery(Request $request)
    {
        $q = Bien::query()->with(['tipoBien', 'documentoSustento']);

        if ($request->filled('desde')) {
            $q->whereDate('fecha_registro', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $q->whereDate('fecha_registro', '<=', $request->hasta);
        }
        if ($request->filled('tipo_bien')) {
            $q->where('id_tipobien', $request->tipo_bien);
        }
        if ($request->filled('documento')) {
            $q->where('id_documento', $request->documento);
        }

        if ($request->filled('con_documento')) {
            if ($request->con_documento === '1' || $request->con_documento == 1) {
                $q->whereNotNull('id_documento');
            }
            if ($request->con_documento === '0' || $request->con_documento == 0) {
                $q->whereNull('id_documento');
            }
        }

        $term = trim((string) $request->input('search.value', ''));
        if ($term === '') {
            $term = trim((string) $request->input('q', ''));
        }

        if ($term !== '') {
            $q->where(function ($w) use ($term) {
                $w->where('codigo_patrimonial', 'ILIKE', "%{$term}%")
                  ->orWhere('denominacion_bien', 'ILIKE', "%{$term}%")
                  ->orWhere('marca_bien', 'ILIKE', "%{$term}%")
                  ->orWhere('modelo_bien', 'ILIKE', "%{$term}%")
                  ->orWhere('nserie_bien', 'ILIKE', "%{$term}%")
                  ->orWhere('NumDoc', 'ILIKE', "%{$term}%");
            });
        }

        return $q;
    }

    private function reportSettings(): array
    {
        $s = [
            'nombre_institucion' => setting('nombre_institucion', ''),
            'ruc' => setting('ruc', ''),
            'direccion' => setting('direccion', ''),
            'telefono' => setting('telefono', ''),
            'pie_reportes' => setting('pie_reportes', ''),
            'texto_legal' => setting('texto_legal', ''),
            'logo_reportes_path' => setting('logo_reportes_path'),
        ];

        $s['logo_reportes_abs'] = null;
        if (!empty($s['logo_reportes_path']) && Storage::disk('public')->exists($s['logo_reportes_path'])) {
            $s['logo_reportes_abs'] = Storage::disk('public')->path($s['logo_reportes_path']);
        }

        return $s;
    }

    private function qrBase64(?string $codigoPatrimonial): string
    {
        $texto = trim((string) $codigoPatrimonial);
        if ($texto === '') {
            $texto = 'SIN-CODIGO';
        }

        $writer = new PngWriter();
        $qr = QrCode::create($texto)
            ->setSize(180)
            ->setMargin(1)
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

        $result = $writer->write($qr);

        return base64_encode($result->getString());
    }

    private function attachQrToBienes($bienes)
    {
        $bienes->each(function ($bien) {
            $bien->qr_code = $this->qrBase64($bien->codigo_patrimonial);
        });

        return $bienes;
    }

    public function index()
    {
        $tiposBien = TipoBien::orderBy('nombre_tipo')->get();
        $documentos = DocumentoSustento::orderBy('fecha_documento', 'desc')->get();
        $settings = $this->reportSettings();

        return view('reportes.bienes.index', compact('tiposBien', 'documentos', 'settings'));
    }

    public function data(Request $request)
    {
        $draw   = (int) $request->input('draw', 1);
        $start  = (int) $request->input('start', 0);
        $length = (int) $request->input('length', 10);

        $base = $this->buildQuery($request);

        $recordsTotal = Bien::query()->count();
        $recordsFiltered = (clone $base)->count();

        $data = $base
            ->orderBy('fecha_registro', 'desc')
            ->orderBy('id_bien', 'desc')
            ->skip($start)
            ->take($length)
            ->get()
            ->map(function ($b) {
                return [
                    'id_bien' => $b->id_bien,
                    'fecha_registro' => optional($b->fecha_registro)->format('Y-m-d'),
                    'codigo_patrimonial' => $b->codigo_patrimonial,
                    'denominacion_bien' => $b->denominacion_bien,
                    'tipo_bien' => optional($b->tipoBien)->nombre_tipo,
                    'marca_bien' => $b->marca_bien,
                    'modelo_bien' => $b->modelo_bien,
                    'nserie_bien' => $b->nserie_bien,
                    'numdoc' => $b->NumDoc,
                    'documento' => optional($b->documentoSustento)->tipo_documento,
                    'numero_documento' => optional($b->documentoSustento)->numero_documento,
                ];
            });

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $recordsTotal,
            'recordsFiltered' => $recordsFiltered,
            'data' => $data,
        ]);
    }

    public function pdf(Request $request)
    {
        $bienes = $this->buildQuery($request)->get();
        $bienes = $this->attachQrToBienes($bienes);

        $settings = $this->reportSettings();

        $pdf = Pdf::loadView('reportes.bienes.pdf', [
            'bienes' => $bienes,
            'settings' => $settings,
            'filtros' => $request->all(),
        ])->setPaper('a4', 'portrait');

        return $pdf->stream('reporte_bienes.pdf');
    }

    public function excel(Request $request)
    {
        $bienes = $this->buildQuery($request)->get();
        $bienes = $this->attachQrToBienes($bienes);

        $settings = $this->reportSettings();

        return Excel::download(
            new BienesExport($bienes, $settings, $request->all()),
            'reporte_bienes.xlsx'
        );
    }
}
