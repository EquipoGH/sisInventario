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
            // ⭐ Aumentar límites para grandes volúmenes de QRs
            set_time_limit(0); 
            ini_set('memory_limit', '2048M');

            $filtro = $request->input('filtro', 'todos');

            // ⭐ CONFIGURACIÓN FIJA PARA GRID 4x4
            $tamanoQR = 90; // Tamaño óptimo para 4x4 en A4
            $qrPorPagina = 16; // 4 filas × 4 columnas

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
                $searchLower = strtolower($search);
                $query->where(function($q) use ($searchLower) {
                    $q->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(marca_bien) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(modelo_bien) LIKE ?', ["%{$searchLower}%"])
                      ->orWhereRaw('LOWER(nserie_bien) LIKE ?', ["%{$searchLower}%"]);
                });
            }

            $bienes = $query->orderBy('codigo_patrimonial')->get();

            if ($bienes->isEmpty()) {
                return back()->with('error', 'No hay bienes para generar códigos QR');
            }

            // ✅ GENERAR QR CODES EN FORMATO SVG (NO REQUIERE IMAGICK)
            $bienesConQR = $bienes->map(function($bien) use ($tamanoQR) {
                $urlAPI = env('API_NODE_URL', 'https://inventario-android-api.onrender.com') . "/qr/{$bien->codigo_patrimonial}";

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

            // ⭐ AGRUPAR EN PÁGINAS DE 9 QR (values() garantiza índices 0, 1, 2...)
            $paginas = $bienesConQR->chunk($qrPorPagina)->values();

            // Construir texto de filtros avanzados
            $filtroPartes = [];
            if ($request->filled('area_id')) {
                $area = \App\Models\Area::find($request->area_id);
                if($area) $filtroPartes[] = "Área: {$area->nombre_area}";
            }
            if ($request->filled('ubicacion_id')) {
                $ubic = \Illuminate\Support\Facades\DB::table('ubicacion')->where('id_ubicacion', $request->ubicacion_id)->first();
                if($ubic) $filtroPartes[] = "Ubic.: {$ubic->ambiente}";
            }
            if ($request->filled('tipo_bien')) {
                $tipo = \App\Models\TipoBien::find($request->tipo_bien);
                if($tipo) $filtroPartes[] = "Tipo: {$tipo->nombre_tipo}";
            }
            if ($request->filled('estado_bien_id')) {
                $est = \App\Models\EstadoBien::find($request->estado_bien_id);
                if($est) $filtroPartes[] = "Estado: {$est->nombre_estado}";
            }
            if ($request->filled('anio')) {
                $filtroPartes[] = "Año: {$request->anio}";
            }
            
            $filtroTexto = !empty($filtroPartes) ? implode(' | ', $filtroPartes) : "Filtro principal: " . $this->getNombreFiltro($filtro);

            // ⭐ GENERAR PDF CON VISTA GRID
            $pdf = Pdf::loadView('qr-bienes.pdf-grid', [
                'paginas' => $paginas,
                'total'   => $bienes->count(),
                'fecha'   => now()->format('d/m/Y H:i'),
                'filtro'  => $filtroTexto,
            ])
            ->setPaper('a4', 'portrait')
            ->setOption('isHtml5ParserEnabled', true)
            ->setOption('isRemoteEnabled', false)
            ->setOption('defaultFont', 'Arial')
            ->setOption('dpi', 96);

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

            $urlAPI = env('API_NODE_URL', 'https://inventario-android-api.onrender.com') . "/qr/{$codigo}";

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
            $urlAPI = env('API_NODE_URL', 'https://inventario-android-api.onrender.com') . "/qr/{$codigo}";

            // Generar QR PNG en memoria
            $qr = new EndroidQrCode($urlAPI);
            $qr->setSize(300);
            $qr->setMargin(10);
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

            $writer  = new PngWriter();
            $result  = $writer->write($qr);

            // Componer imagen con logo + textos
            $imgData = $this->componerImagenQR(
                $result->getString(),
                $bien->denominacion_bien,
                $bien->codigo_patrimonial
            );

            $base64 = 'data:image/png;base64,' . base64_encode($imgData);

            return response()->json([
                'ok'     => true,
                'qr_img' => $base64,
                'codigo' => $bien->codigo_patrimonial,
                'nombre' => $bien->denominacion_bien,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'ok'      => false,
                'message' => 'Error al generar código QR: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * 📥 DESCARGAR IMAGEN QR INDIVIDUAL (con logo + textos)
     */
    public function descargarImagenQR($codigo)
    {
        try {
            $bien = Bien::where('codigo_patrimonial', $codigo)->firstOrFail();
            $urlAPI = env('API_NODE_URL', 'https://inventario-android-api.onrender.com') . "/qr/{$codigo}";

            $qr = new EndroidQrCode($urlAPI);
            $qr->setSize(520); // Mayor tamaño para impresión
            $qr->setMargin(10);
            $qr->setErrorCorrectionLevel(ErrorCorrectionLevel::High);

            $writer = new PngWriter();
            $result = $writer->write($qr);

            // Componer imagen con logo + textos
            $imgData = $this->componerImagenQR(
                $result->getString(),
                $bien->denominacion_bien,
                $bien->codigo_patrimonial
            );

            $nombreArchivo = "QR_{$codigo}_" . date('Ymd_His') . ".png";

            return response($imgData)
                ->header('Content-Type', 'image/png')
                ->header('Content-Disposition', 'attachment; filename="' . $nombreArchivo . '"');

        } catch (\Exception $e) {
            return back()->with('error', 'Error al descargar QR: ' . $e->getMessage());
        }
    }

    /**
     * 🎨 COMPONER IMAGEN QR: superpone logo en el centro + nombre + código abajo
     */
    private function componerImagenQR(string $qrPngData, string $nombre, string $codigo): string
    {
        // ── 1. Cargar QR base ───────────────────────────────────────────────
        $qrImg = imagecreatefromstring($qrPngData);
        $qrW   = imagesx($qrImg);
        $qrH   = imagesy($qrImg);

        // ── 2. Definir panel inferior de texto ──────────────────────────────
        $paddingTop    = 14;  // espacio entre QR y texto
        $paddingBottom = 18;
        $lineHeight    = 28;
        $fontSize      = 5;   // fuente GD interna (1-5)
        $charW         = imagefontwidth($fontSize);
        $charH         = imagefontheight($fontSize);

        // Truncar nombre si es muy largo
        $maxChars = (int) floor($qrW / $charW) - 2;
        $nombreTxt = mb_strlen($nombre) > $maxChars
            ? mb_substr($nombre, 0, $maxChars - 3) . '...'
            : $nombre;
        $codigoTxt = $codigo;

        $panelH = $paddingTop + $charH + 6 + $charH + $paddingBottom;

        // ── 3. Crear lienzo final (QR + panel) ──────────────────────────────
        $canvas = imagecreatetruecolor($qrW, $qrH + $panelH);
        imagealphablending($canvas, true);
        imagesavealpha($canvas, true);

        // Colores
        $blanco    = imagecolorallocate($canvas, 255, 255, 255);
        $grisClaro = imagecolorallocate($canvas, 245, 247, 250);
        $azulOsc   = imagecolorallocate($canvas, 30,  58, 138);  // azul institucional
        $grisTexto = imagecolorallocate($canvas, 55,  65,  81);
        $bordeColor= imagecolorallocate($canvas, 99, 102, 241);  // índigo

        // Fondo blanco completo
        imagefilledrectangle($canvas, 0, 0, $qrW - 1, $qrH + $panelH - 1, $blanco);

        // Panel inferior con color suave
        imagefilledrectangle($canvas, 0, $qrH, $qrW - 1, $qrH + $panelH - 1, $grisClaro);

        // Línea separadora decorativa (índigo)
        imagefilledrectangle($canvas, 0, $qrH, $qrW - 1, $qrH + 3, $bordeColor);

        // Pegar QR
        imagecopy($canvas, $qrImg, 0, 0, 0, 0, $qrW, $qrH);
        imagedestroy($qrImg);

        // ── 4. Superponer LOGO en el centro del QR ──────────────────────────
        $logoPath = public_path('images/bienes/LogosinFondo.png');
        if (file_exists($logoPath)) {
            $logoSrc = @imagecreatefrompng($logoPath);
            if ($logoSrc) {
                // Tamaño del logo: ~18% del QR
                $logoSize = (int) round($qrW * 0.18);
                $logoDst  = imagecreatetruecolor($logoSize, $logoSize);
                imagealphablending($logoDst, false);
                imagesavealpha($logoDst, true);
                $trans = imagecolorallocatealpha($logoDst, 255, 255, 255, 127);
                imagefilledrectangle($logoDst, 0, 0, $logoSize - 1, $logoSize - 1, $trans);
                imagealphablending($logoDst, true);
                imagecopyresampled(
                    $logoDst, $logoSrc,
                    0, 0, 0, 0,
                    $logoSize, $logoSize,
                    imagesx($logoSrc), imagesy($logoSrc)
                );
                imagedestroy($logoSrc);

                // Fondo blanco circular detrás del logo
                $cx = (int) round($qrW / 2);
                $cy = (int) round($qrH / 2);
                $r  = (int) round($logoSize / 2) + 6;
                imagefilledellipse($canvas, $cx, $cy, $r * 2, $r * 2, $blanco);

                // Pegar logo centrado en el QR
                $lx = $cx - (int) round($logoSize / 2);
                $ly = $cy - (int) round($logoSize / 2);
                imagecopy($canvas, $logoDst, $lx, $ly, 0, 0, $logoSize, $logoSize);
                imagedestroy($logoDst);
            }
        }

        // ── 5. Escribir textos en el panel inferior ──────────────────────────
        // Nombre del bien (azul, centrado)
        $nombreW = strlen($nombreTxt) * $charW;
        $nombreX = (int) max(0, round(($qrW - $nombreW) / 2));
        $nombreY = $qrH + $paddingTop;
        imagestring($canvas, $fontSize, $nombreX, $nombreY, $nombreTxt, $azulOsc);

        // Código patrimonial (gris oscuro, centrado, debajo del nombre)
        $codigoW = strlen($codigoTxt) * $charW;
        $codigoX = (int) max(0, round(($qrW - $codigoW) / 2));
        $codigoY = $nombreY + $charH + 6;
        imagestring($canvas, $fontSize, $codigoX, $codigoY, $codigoTxt, $grisTexto);

        // ── 6. Exportar a string PNG ─────────────────────────────────────────
        ob_start();
        imagepng($canvas, null, 6); // compresión 6
        $output = ob_get_clean();
        imagedestroy($canvas);

        return $output;
    }
}
