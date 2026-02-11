<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\TipoBien;
use App\Models\DocumentoSustento;
use App\Models\Movimiento;
use App\Models\TipoMvto;
use App\Models\EstadoBien;
use App\Http\Requests\BienRequest;
use App\Services\MovimientoService;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class BienController extends Controller
{
    protected $movimientoService;

    public function __construct(MovimientoService $movimientoService)
    {
        $this->movimientoService = $movimientoService;
    }

    /**
     * ✅ Listar SOLO bienes ACTIVOS
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // ✅ Total de bienes ACTIVOS
        $total = Bien::activos()->count();

        // ✅ SOLO BIENES ACTIVOS
        $query = Bien::with(['tipoBien', 'documentoSustento'])
            ->activos(); // ⭐ CAMBIO CRÍTICO

        // 🔍 BÚSQUEDA AVANZADA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_bien', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_patrimonial', 'ILIKE', "%{$search}%")
                  ->orWhere('denominacion_bien', 'ILIKE', "%{$search}%")
                  ->orWhere('marca_bien', 'ILIKE', "%{$search}%")
                  ->orWhere('modelo_bien', 'ILIKE', "%{$search}%")
                  ->orWhere('NumDoc', 'ILIKE', "%{$search}%")
                  ->orWhereHas('tipoBien', function($q) use ($search) {
                      $q->where('nombre_tipo', 'ILIKE', "%{$search}%");
                  })
                  ->orWhereHas('documentoSustento', function($q) use ($search) {
                      $q->where('numero_documento', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // 📊 ORDENAMIENTO
        $columna = $request->get('orden', 'created_at');
        $direccion = $request->get('direccion', 'desc');

        $columnasPermitidas = [
            'codigo' => 'codigo_patrimonial',
            'denominacion' => 'denominacion_bien',
            'fecha' => 'created_at',
            'numdoc' => 'NumDoc'
        ];

        $columnaReal = $columnasPermitidas[$columna] ?? 'created_at';
        $direccion = in_array(strtolower($direccion), ['asc', 'desc']) ? strtolower($direccion) : 'desc';

        $query->orderBy($columnaReal, $direccion);

        $bienes = $query->paginate($perPage);
        $tiposBien = TipoBien::orderBy('nombre_tipo')->get();

        // ⭐ PETICIÓN AJAX
        if ($request->ajax()) {
            $data = $bienes->map(function($bien) {
                return [
                    'id_bien' => $bien->id_bien,
                    'codigo_patrimonial' => $bien->codigo_patrimonial,
                    'denominacion_bien' => $bien->denominacion_bien,
                    'id_tipobien' => $bien->id_tipobien,
                    'modelo_bien' => $bien->modelo_bien,
                    'marca_bien' => $bien->marca_bien,
                    'color_bien' => $bien->color_bien,
                    'dimensiones_bien' => $bien->dimensiones_bien,
                    'nserie_bien' => $bien->nserie_bien,
                    'fecha_registro' => $bien->fecha_registro,
                    'foto_bien' => $bien->foto_bien,
                    'NumDoc' => $bien->NumDoc,
                    'activo' => $bien->activo, // ✅
                    'tipo_bien' => $bien->tipoBien ? [
                        'id_tipo_bien' => $bien->tipoBien->id_tipo_bien,
                        'nombre_tipo' => $bien->tipoBien->nombre_tipo
                    ] : null,
                    'documento_sustento' => $bien->documentoSustento ? [
                        'id_documento' => $bien->documentoSustento->id_documento,
                        'tipo_documento' => $bien->documentoSustento->tipo_documento,
                        'numero_documento' => $bien->documentoSustento->numero_documento,
                        'NumDoc' => $bien->documentoSustento->numero_documento
                    ] : null
                ];
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'total' => $total,
                'resultados' => $bienes->total(),
                'current_page' => $bienes->currentPage(),
                'last_page' => $bienes->lastPage(),
                'per_page' => $bienes->perPage(),
                'from' => $bienes->firstItem(),
                'to' => $bienes->lastItem()
            ]);
        }

        return view('bien.index', compact('bienes', 'tiposBien', 'total'));
    }

    /**
     * Guardar nuevo bien
     */
    public function store(BienRequest $request)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // 📸 Subir imagen a Cloudinary
            if ($request->hasFile('foto_bien')) {
                $uploadedFile = Cloudinary::upload(
                    $request->file('foto_bien')->getRealPath(),
                    [
                        'folder' => 'bienes',
                        'transformation' => [
                            'width' => 800,
                            'height' => 800,
                            'crop' => 'limit',
                            'quality' => 'auto:best',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $data['foto_bien'] = $uploadedFile->getSecurePath();
                $data['public_id'] = $uploadedFile->getPublicId();
            }

            $bien = Bien::create($data);
            $bien->load('documentoSustento', 'tipoBien');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bien registrado exitosamente',
                'data' => [
                    'id_bien' => $bien->id_bien,
                    'codigo_patrimonial' => $bien->codigo_patrimonial,
                    'denominacion_bien' => $bien->denominacion_bien,
                    'id_tipobien' => $bien->id_tipobien,
                    'modelo_bien' => $bien->modelo_bien,
                    'marca_bien' => $bien->marca_bien,
                    'color_bien' => $bien->color_bien,
                    'dimensiones_bien' => $bien->dimensiones_bien,
                    'nserie_bien' => $bien->nserie_bien,
                    'fecha_registro' => $bien->fecha_registro,
                    'foto_bien' => $bien->foto_bien,
                    'id_documento' => $bien->id_documento,
                    'NumDoc' => $bien->NumDoc,
                    'tipo_bien' => $bien->tipoBien ? [
                        'id_tipo_bien' => $bien->tipoBien->id_tipo_bien,
                        'nombre_tipo' => $bien->tipoBien->nombre_tipo
                    ] : null,
                    'documento_sustento' => $bien->documentoSustento ? [
                        'id_documento' => $bien->documentoSustento->id_documento,
                        'tipo_documento' => $bien->documentoSustento->tipo_documento,
                        'numero_documento' => $bien->documentoSustento->numero_documento
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear bien:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener datos para editar
     */
    public function edit(Bien $bien)
    {
        $bien->load(['tipoBien', 'documentoSustento']);

        return response()->json([
            'id_bien' => $bien->id_bien,
            'codigo_patrimonial' => $bien->codigo_patrimonial,
            'denominacion_bien' => $bien->denominacion_bien,
            'id_tipobien' => $bien->id_tipobien,
            'id_documento' => $bien->id_documento,
            'modelo_bien' => $bien->modelo_bien,
            'marca_bien' => $bien->marca_bien,
            'color_bien' => $bien->color_bien,
            'dimensiones_bien' => $bien->dimensiones_bien,
            'nserie_bien' => $bien->nserie_bien,
            'fecha_registro' => $bien->fecha_registro,
            'foto_bien' => $bien->foto_bien,
            'NumDoc' => $bien->NumDoc,
        ]);
    }

    /**
     * Actualizar bien existente
     */
    public function update(BienRequest $request, Bien $bien)
    {
        try {
            DB::beginTransaction();

            $data = $request->validated();

            // 📸 Si hay nueva imagen
            if ($request->hasFile('foto_bien')) {
                if ($bien->public_id) {
                    try {
                        Cloudinary::destroy($bien->public_id);
                    } catch (\Exception $e) {
                        Log::warning('Error al eliminar imagen anterior: ' . $e->getMessage());
                    }
                }

                $uploadedFile = Cloudinary::upload(
                    $request->file('foto_bien')->getRealPath(),
                    [
                        'folder' => 'bienes',
                        'transformation' => [
                            'width' => 800,
                            'height' => 800,
                            'crop' => 'limit',
                            'quality' => 'auto:best',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $data['foto_bien'] = $uploadedFile->getSecurePath();
                $data['public_id'] = $uploadedFile->getPublicId();
            }

            $bien->update($data);
            $bien->load('documentoSustento', 'tipoBien');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bien actualizado exitosamente',
                'data' => [
                    'id_bien' => $bien->id_bien,
                    'codigo_patrimonial' => $bien->codigo_patrimonial,
                    'denominacion_bien' => $bien->denominacion_bien,
                    'id_tipobien' => $bien->id_tipobien,
                    'modelo_bien' => $bien->modelo_bien,
                    'marca_bien' => $bien->marca_bien,
                    'color_bien' => $bien->color_bien,
                    'dimensiones_bien' => $bien->dimensiones_bien,
                    'nserie_bien' => $bien->nserie_bien,
                    'fecha_registro' => $bien->fecha_registro,
                    'foto_bien' => $bien->foto_bien,
                    'id_documento' => $bien->id_documento,
                    'NumDoc' => $bien->NumDoc,
                    'tipo_bien' => $bien->tipoBien ? [
                        'id_tipo_bien' => $bien->tipoBien->id_tipo_bien,
                        'nombre_tipo' => $bien->tipoBien->nombre_tipo
                    ] : null,
                    'documento_sustento' => $bien->documentoSustento ? [
                        'id_documento' => $bien->documentoSustento->id_documento,
                        'tipo_documento' => $bien->documentoSustento->tipo_documento,
                        'numero_documento' => $bien->documentoSustento->numero_documento
                    ] : null
                ]
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar bien:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * ✅ ELIMINAR LÓGICO CON VERIFICACIÓN DE MOVIMIENTOS
     */
    public function destroy(Bien $bien)
    {
        try {
            // ⭐ Verificar si tiene movimientos
            $tieneMovimientos = $bien->movimientos()->exists();
            
            // Eliminar lógicamente
            $bien->eliminarLogico();
            
            return response()->json([
                'success' => true,
                'message' => 'Bien eliminado correctamente',
                'tenia_movimientos' => $tieneMovimientos
            ]);

        } catch (\Exception $e) {
            Log::error('Error al eliminar bien:', [
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * ✅ NUEVO: Ver bienes eliminados (para modal)
     */
    public function eliminados(Request $request)
    {
        try {
            $search = $request->input('search', '');

            $bienes = Bien::with('tipoBien')
                ->eliminados() // ✅ Solo inactivos
                ->when($search, function($query, $search) {
                    $query->where(function($q) use ($search) {
                        $q->where('codigo_patrimonial', 'ILIKE', "%{$search}%")
                          ->orWhere('denominacion_bien', 'ILIKE', "%{$search}%");
                    });
                })
                ->orderBy('eliminado_en', 'desc')
                ->paginate(10);

            return response()->json([
                'success' => true,
                'data' => $bienes
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener eliminados:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al cargar bienes eliminados'
            ], 500);
        }
    }

    /**
     * ✅ NUEVO: Restaurar bien eliminado
     */
    public function restaurar($id)
    {
        try {
            $bien = Bien::findOrFail($id);
            $bien->restaurar();

            return response()->json([
                'success' => true,
                'message' => 'Bien restaurado correctamente'
            ]);

        } catch (\Exception $e) {
            Log::error('Error al restaurar bien:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al restaurar bien'
            ], 500);
        }
    }


    /**
     * ⭐⭐⭐ OBTENER ÚLTIMO MOVIMIENTO ANTES DE ELIMINAR ⭐⭐⭐
     */
    public function obtenerUltimoMovimiento(Bien $bien)
    {
        try {
            // Obtener último movimiento con todas las relaciones
            $ultimoMov = $bien->movimientos()
                ->with([
                    'tipoMovimiento',
                    'ubicacion.area',
                    'estadoConservacion',
                    'usuario'
                ])
                ->where('anulado', false)  // ⭐ Solo movimientos vigentes
                ->orderBy('fecha_mvto', 'desc')
                ->first();

            // Si no tiene movimientos
            if (!$ultimoMov) {
                return response()->json([
                    'success' => true,
                    'tiene_movimientos' => false,
                    'solo_registro' => false,
                    'message' => 'El bien no tiene movimientos registrados',
                    'bien' => [
                        'codigo' => $bien->codigo_patrimonial,
                        'denominacion' => $bien->denominacion_bien
                    ]
                ]);
            }

            // ⭐⭐⭐ DETECTAR SI SOLO TIENE MOVIMIENTO INICIAL (SIN ASIGNAR) ⭐⭐⭐
            $totalMovimientos = $bien->movimientos()->where('anulado', false)->count();
            $tipoMovimiento = strtoupper($ultimoMov->tipoMovimiento->tipo_mvto ?? '');
            
            $esSoloRegistro = (
                $totalMovimientos === 1 && 
                (
                    str_contains($tipoMovimiento, 'REGISTRO') ||
                    str_contains($tipoMovimiento, 'SIN ASIGNAR') ||
                    str_contains($tipoMovimiento, 'ALTA')
                )
            );

            // ⭐ CASO ESPECIAL: Solo tiene movimiento de registro inicial
            if ($esSoloRegistro) {
                return response()->json([
                    'success' => true,
                    'tiene_movimientos' => false,  // ⭐ Se comporta como si no tuviera
                    'solo_registro' => true,
                    'message' => 'El bien está registrado pero sin asignar',
                    'bien' => [
                        'codigo' => $bien->codigo_patrimonial,
                        'denominacion' => $bien->denominacion_bien
                    ],
                    'movimiento_inicial' => [
                        'tipo' => $ultimoMov->tipoMovimiento->tipo_mvto ?? 'N/A',
                        'fecha' => \Carbon\Carbon::parse($ultimoMov->fecha_mvto)->format('d/m/Y H:i'),
                        'usuario' => $ultimoMov->usuario->name ?? 'Sistema'
                    ]
                ]);
            }

            // ⭐ CASO NORMAL: Tiene movimientos reales (asignaciones, bajas, etc.)
            return response()->json([
                'success' => true,
                'tiene_movimientos' => true,
                'solo_registro' => false,
                'bien' => [
                    'codigo' => $bien->codigo_patrimonial,
                    'denominacion' => $bien->denominacion_bien
                ],
                'ultimo_movimiento' => [
                    'tipo' => $ultimoMov->tipoMovimiento->tipo_mvto ?? 'N/A',
                    'tipo_badge' => $this->getBadgeTipoMovimiento($ultimoMov->tipoMovimiento->tipo_mvto ?? ''),
                    'area' => $ultimoMov->ubicacion->area->nombre_area ?? 'Sin área',
                    'ubicacion' => $ultimoMov->ubicacion->nombre_sede ?? 'Sin ubicación',
                    'estado_conservacion' => $ultimoMov->estadoConservacion->nombre_estado ?? 'Sin estado',
                    'estado_badge' => $this->getBadgeEstadoConservacion($ultimoMov->estadoConservacion->nombre_estado ?? ''),
                    'fecha' => \Carbon\Carbon::parse($ultimoMov->fecha_mvto)->format('d/m/Y H:i'),
                    'usuario' => $ultimoMov->usuario->name ?? 'Sistema'
                ],
                'estadisticas' => [
                    'total_movimientos' => $bien->movimientos()->count(),
                    'movimientos_vigentes' => $bien->movimientos()->where('anulado', false)->count(),
                    'movimientos_anulados' => $bien->movimientos()->where('anulado', true)->count()
                ]
            ]);

        } catch (\Exception $e) {
            Log::error('Error al obtener último movimiento:', [
                'error' => $e->getMessage(),
                'bien_id' => $bien->id_bien
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener información del movimiento'
            ], 500);
        }
    }


    /**
     * ⭐ HELPER: Obtener clase de badge según tipo de movimiento
     */
    private function getBadgeTipoMovimiento($tipo)
    {
        $tipo = strtoupper($tipo);
        
        if (str_contains($tipo, 'ASIGNACIÓN') || str_contains($tipo, 'ASIGNACION')) {
            return 'badge-success';
        } elseif (str_contains($tipo, 'BAJA')) {
            return 'badge-danger';
        } elseif (str_contains($tipo, 'REGISTRO')) {
            return 'badge-info';
        } else {
            return 'badge-secondary';
        }
    }

    /**
     * ⭐ HELPER: Obtener clase de badge según estado de conservación
     */
    private function getBadgeEstadoConservacion($estado)
    {
        $estado = strtoupper($estado);
        
        if (str_contains($estado, 'BUENO') || str_contains($estado, 'EXCELENTE')) {
            return 'badge-success';
        } elseif (str_contains($estado, 'REGULAR')) {
            return 'badge-warning';
        } elseif (str_contains($estado, 'MALO') || str_contains($estado, 'DETERIORADO')) {
            return 'badge-danger';
        } else {
            return 'badge-secondary';
        }
    }




    /**
     * Verificar si el código patrimonial ya existe
     */
    public function verificarCodigo(Request $request)
    {
        $codigo = $request->codigo;
        $id = $request->id;

        $existe = Bien::where('codigo_patrimonial', $codigo)
            ->when($id, function($query) use ($id) {
                return $query->where('id_bien', '!=', $id);
            })
            ->exists();

        return response()->json([
            'existe' => $existe,
            'disponible' => !$existe
        ]);
    }

    /**
     * Obtener documentos para SELECT en formulario
     */
    public function obtenerDocumentos()
    {
        try {
            $documentos = DocumentoSustento::select('id_documento', 'tipo_documento', 'numero_documento', 'fecha_documento')
                ->orderBy('fecha_documento', 'desc')
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id_documento,
                        'text' => "{$doc->tipo_documento} - {$doc->numero_documento}"
                    ];
                });

            return response()->json($documentos);

        } catch (\Exception $e) {
            Log::error('Error al obtener documentos:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener documentos'
            ], 500);
        }
    }
}
