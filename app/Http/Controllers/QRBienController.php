<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use Barryvdh\DomPDF\Facade\Pdf;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Endroid\QrCode\QrCode as EndroidQrCode;
use Endroid\QrCode\Color\Color;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\RoundBlockSizeMode;
use Endroid\QrCode\Writer\PngWriter;
use App\Models\Area;
use App\Models\TipoBien;
use App\Models\EstadoBien;

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

        $areas = Area::orderBy('nombre_area')->get();
        $tiposBien = TipoBien::orderBy('nombre_tipo')->get();
        $estadosBien = EstadoBien::orderBy('nombre_estado')->get();
        
        $anios = Bien::selectRaw('EXTRACT(YEAR FROM created_at) as anio')
            ->distinct()
            ->orderBy('anio', 'desc')
            ->pluck('anio');

        return view('qr-bienes.index', compact('totalBienes', 'bienesConMovimiento', 'areas', 'tiposBien', 'estadosBien', 'anios'));
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

            // Aplicar filtro adicional/antiguo
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

            // ⭐ NUEVOS FILTROS AVANZADOS (Si vienen en el request)
            if ($request->filled('area_id')) {
                $query->whereHas('movimientos', function($q) use ($request) {
                    $q->where('anulado', false)
                      ->whereHas('ubicacion', function($q2) use ($request) {
                          // Se corrige "id_area" a "idarea" que es el nombre real en bd
                          $q2->where('idarea', $request->area_id);
                      });
                });
            }

            if ($request->filled('ubicacion_id')) {
                $query->whereHas('movimientos', function($q) use ($request) {
                    $q->where('anulado', false)->where('id_ubicacion', $request->ubicacion_id);
                });
            }

            if ($request->filled('tipo_bien')) {
                $query->where('id_tipobien', $request->tipo_bien);
            }

            if ($request->filled('estado_bien_id')) {
                $query->whereHas('movimientos', function($q) use ($request) {
                    // El valor proviene del select2 y la bd lo guarda en id_estado_conservacion_bien en tabla movimiento
                    $q->where('anulado', false)->where('id_estado_conservacion_bien', $request->estado_bien_id);
                });
            }

            if ($request->filled('anio')) {
                $query->whereYear('created_at', $request->anio);
            }

            if ($request->filled('q')) {
                $search = $request->q;
                $query->where(function($q) use ($search) {
                    $q->where('codigo_patrimonial', 'ILIKE', "%{$search}%")
                      ->orWhere('denominacion_bien', 'ILIKE', "%{$search}%")
                      ->orWhere('marca_bien', 'ILIKE', "%{$search}%")
                      ->orWhere('modelo_bien', 'ILIKE', "%{$search}%")
                      ->orWhere('nserie_bien', 'ILIKE', "%{$search}%");
                });
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
    /**
     * 🖼️ OBTENER IMAGEN QR INDIVIDUAL PARA MOSTRARLA EN EL NAVEGADOR
     */
    public function verImagenQR($codigo)
    {
        try {
            $bien = Bien::where('codigo_patrimonial', $codigo)->firstOrFail();
            $urlAPI = "https://web-production-84102.up.railway.app/qr/{$codigo}";
            
            // Generar el código QR en base64 para enviarlo al frontend mediante EndroidQrCode para generar un PNG
            $qr = new EndroidQrCode($urlAPI);
            $qr->setSize(300);
            $qr->setMargin(10);
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
            
            $writer = new PngWriter();
            $result = $writer->write($qr);

            return response()->json([
                'ok' => true,
                'qr_img' => $result->getDataUri(),
                'codigo' => $bien->codigo_patrimonial,
                'nombre' => $bien->denominacion_bien,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok' => false,
                'message' => 'Error al generar código QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 DESCARGAR IMAGEN QR INDIVIDUAL
     */
    public function descargarImagenQR($codigo)
    {
        try {
            $bien = Bien::where('codigo_patrimonial', $codigo)->firstOrFail();
            $urlAPI = "https://web-production-84102.up.railway.app/qr/{$codigo}";
            
            $qr = new EndroidQrCode($urlAPI);
            $qr->setSize(500); // Mayor tamaño para impresión
            $qr->setMargin(10);
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::High);
            
            $writer = new PngWriter();
            $result = $writer->write($qr);
                
            $nombreArchivo = "QR_{$codigo}_" . date('Ymd_His') . ".png";
            
            return response($result->getString())
                ->header('Content-Type', $result->getMimeType())
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar QR: ' . $e->getMessage());
        }
    }
}
