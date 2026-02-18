<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class QRBienController extends Controller
{
    /**
     * 📄 Mostrar vista principal con estadísticas
     */
    public function index()
    {
        // Usar scope 'activos' si existe, si no, filtrar manualmente
        $totalBienes = Bien::where(function($query) {
            // Si tienes scope activos(), úsalo. Si no, filtra por deleted_at
            if (method_exists(Bien::class, 'scopeActivos')) {
                $query->activos();
            } else {
                $query->whereNull('deleted_at');
            }
        })->count();

        $bienesConMovimiento = Bien::where(function($query) {
            if (method_exists(Bien::class, 'scopeActivos')) {
                $query->activos();
            } else {
                $query->whereNull('deleted_at');
            }
        })
        ->whereHas('movimientos', function($q) {
            $q->where('anulado', false);
        })
        ->count();

        return view('qr-bienes.index', compact('totalBienes', 'bienesConMovimiento'));
    }

    /**
     * 🖨️ GENERAR PDF CON QR EN GRID 3x3 (9 POR PÁGINA)
     */
    public function generarPDFMasivo(Request $request)
    {
        try {
            $filtro = $request->input('filtro', 'todos');

            // ⭐ CONFIGURACIÓN FIJA PARA GRID 3x3
            $tamanoQR = 120; // Tamaño óptimo para 3x3 en A4
            $qrPorPagina = 9; // 3 filas × 3 columnas

            // ⭐ CONSULTAR BIENES SEGÚN FILTRO
            $query = Bien::with([
                'tipoBien',
                'movimientos' => function($q) {
                    $q->where('anulado', false)
                      ->orderBy('fecha_mvto', 'desc')
                      ->limit(1);
                },
                'movimientos.ubicacion' // ⭐ EAGER LOADING para optimizar
            ]);

            // Filtrar solo activos
            if (method_exists(Bien::class, 'scopeActivos')) {
                $query->activos();
            } else {
                $query->whereNull('deleted_at');
            }

            // Aplicar filtro adicional
            switch ($filtro) {
                case 'con_movimiento':
                    $query->whereHas('movimientos', function($q) {
                        $q->where('anulado', false);
                    });
                    break;
                case 'sin_movimiento':
                    $query->whereDoesntHave('movimientos');
                    break;
            }

            $bienes = $query->orderBy('codigo_patrimonial')->get();

            if ($bienes->isEmpty()) {
                return back()->with('error', 'No hay bienes para generar códigos QR');
            }

            // ✅ GENERAR QR CODES EN FORMATO SVG (NO REQUIERE IMAGICK)
            $bienesConQR = $bienes->map(function($bien) use ($tamanoQR) {
                $urlAPI = "https://web-production-84102.up.railway.app/qr/{$bien->codigo_patrimonial}";

                // ✅ CAMBIO: PNG → SVG (Compatible sin Imagick)
                $qrCodeSVG = QrCode::format('svg')
                    ->size($tamanoQR)
                    ->errorCorrection('H')
                    ->margin(1)
                    ->generate($urlAPI);

                // ✅ Formato base64 para SVG
                $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCodeSVG);

                // Obtener último movimiento
                $ultimoMovimiento = $bien->movimientos->first();

                return [
                    'codigo' => $bien->codigo_patrimonial,
                    'denominacion' => \Illuminate\Support\Str::limit($bien->denominacion_bien, 45, '...'),
                    'tipo' => $bien->tipoBien->nombre_tipo ?? 'N/A',
                    'qr_base64' => $qrCodeBase64,
                    'tiene_movimiento' => $ultimoMovimiento ? true : false,
                    'ubicacion' => $ultimoMovimiento
                        ? \Illuminate\Support\Str::limit($ultimoMovimiento->ubicacion->ambiente ?? 'Sin ubicación', 35, '...')
                        : 'Sin asignar'
                ];
            });

            // ⭐ AGRUPAR EN PÁGINAS DE 9 QR
            $paginas = $bienesConQR->chunk($qrPorPagina);

            // ⭐ GENERAR PDF CON VISTA GRID
            $pdf = Pdf::loadView('qr-bienes.pdf-grid', [
                'paginas' => $paginas,
                'total' => $bienes->count(),
                'fecha' => now()->format('d/m/Y H:i'),
                'filtro' => $this->getNombreFiltro($filtro),
                'qrPorPagina' => $qrPorPagina,
                'totalPaginas' => $paginas->count()
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', true)
            ->setOption('defaultFont', 'Arial');

            $nombreArchivo = 'QR_Bienes_' . now()->format('Ymd_His') . '.pdf';

            // ⭐ LOG DE ÉXITO
            Log::info('PDF de QR generado exitosamente', [
                'total_bienes' => $bienes->count(),
                'filtro' => $filtro,
                'total_paginas' => $paginas->count(),
                'archivo' => $nombreArchivo,
                'formato' => 'SVG' // ✅ Indicar formato usado
            ]);

            return $pdf->download($nombreArchivo);

        } catch (\Exception $e) {
            // ⭐ LOG DETALLADO DEL ERROR
            Log::error('Error generando PDF de QR:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile(),
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Error al generar PDF: ' . $e->getMessage());
        }
    }

    /**
     * 🔍 PREVISUALIZAR QR DE UN BIEN INDIVIDUAL
     */
    public function previsualizar($codigo)
    {
        try {
            $bien = Bien::where('codigo_patrimonial', $codigo)->firstOrFail();

            $urlAPI = "https://web-production-84102.up.railway.app/qr/{$codigo}";

            // Generar QR en SVG para previsualización (escalable)
            $qrCode = QrCode::format('svg')
                ->size(300)
                ->errorCorrection('H')
                ->generate($urlAPI);

            return view('qr-bienes.preview', compact('bien', 'qrCode', 'urlAPI'));

        } catch (\Exception $e) {
            Log::error('Error en previsualización de QR:', [
                'codigo' => $codigo,
                'error' => $e->getMessage()
            ]);

            return back()->with('error', 'Bien no encontrado');
        }
    }

    /**
     * 📊 HELPER: Obtener nombre legible del filtro
     */
    private function getNombreFiltro($filtro)
    {
        return match($filtro) {
            'con_movimiento' => 'Solo con movimientos',
            'sin_movimiento' => 'Solo sin asignar',
            default => 'Todos los bienes'
        };
    }

    /**
     * 🔢 API: Obtener estadísticas (para AJAX)
     */
    public function getEstadisticas()
    {
        try {
            $totalBienes = Bien::where(function($query) {
                if (method_exists(Bien::class, 'scopeActivos')) {
                    $query->activos();
                } else {
                    $query->whereNull('deleted_at');
                }
            })->count();

            $conMovimiento = Bien::where(function($query) {
                if (method_exists(Bien::class, 'scopeActivos')) {
                    $query->activos();
                } else {
                    $query->whereNull('deleted_at');
                }
            })
            ->whereHas('movimientos', function($q) {
                $q->where('anulado', false);
            })
            ->count();

            $sinMovimiento = $totalBienes - $conMovimiento;

            return response()->json([
                'ok' => true,
                'data' => [
                    'total' => $totalBienes,
                    'con_movimiento' => $conMovimiento,
                    'sin_movimiento' => $sinMovimiento
                ]
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error obteniendo estadísticas'
            ], 500);
        }
    }
}
