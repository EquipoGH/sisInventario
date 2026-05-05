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
use App\Helpers\PermisosHelper;
use Illuminate\Support\Facades\Auth; // ⭐⭐⭐ NUEVO ⭐⭐⭐

class BienController extends Controller
{
    protected $movimientoService;

    public function __construct(MovimientoService $movimientoService)
    {
        $this->movimientoService = $movimientoService;
    }

    /**
     * ✅ Listar SOLO bienes ACTIVOS con permisos por área
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // ⭐⭐⭐ FILTRAR POR PERMISOS DEL USUARIO (SIN ->activos()) ⭐⭐⭐
        $user = Auth::user();
        $query = PermisosHelper::getBienesQuery()
            ->with(['tipoBien', 'documentoSustento'])
            ->where('activo', true);

        // Si no es ADMIN, limitar bienes a las áreas asignadas al responsable
        if ($user && !$user->esAdmin()) {
            $idsAreas = $user->getIdsAreasPermitidas();
            if (empty($idsAreas)) {
                // Usuario sin áreas: no ver resultados
                $query->whereRaw('0 = 1');
            } else {
                // Mostrar bienes que tengan movimientos vigentes en las áreas permitidas
                $query->whereHas('movimientos', function ($q) use ($idsAreas) {
                    $q->where('anulado', false)
                        ->whereHas('ubicacion', function ($q2) use ($idsAreas) {
                            $q2->whereIn('idarea', $idsAreas);
                        });
                });
            }
        }

        // Total de bienes que el usuario puede ver
        $total = $query->count();

        // 🔍 BÚSQUEDA AVANZADA
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $query->where(function($q) use ($search, $searchLower) {
                $q->where('id_bien', 'LIKE', "%{$search}%")
                ->orWhereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(marca_bien) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(modelo_bien) LIKE ?', ["%{$searchLower}%"])
                ->orWhereRaw('LOWER(NumDoc) LIKE ?', ["%{$searchLower}%"])
                ->orWhereHas('tipoBien', function($q) use ($searchLower) {
                    $q->whereRaw('LOWER(nombre_tipo) LIKE ?', ["%{$searchLower}%"]);
                })
                ->orWhereHas('documentoSustento', function($q) use ($searchLower) {
                    $q->whereRaw('LOWER(numero_documento) LIKE ?', ["%{$searchLower}%"]);
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
        $estadosConservacion = \App\Models\EstadoConservacion::orderBy('nombre_conservacion')->get();

        // ⭐ PETICIÓN AJAX
        if ($request->ajax()) {
            /** @var \Illuminate\Pagination\LengthAwarePaginator $bienes */
            $data = $bienes->getCollection()->map(function($bien) {
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
                    'activo' => $bien->activo,
                    'id_estado_conservacion' => $bien->id_estado_conservacion,
                    'estado_conservacion' => $bien->estadoConservacion
                        ? $bien->estadoConservacion->nombre_conservacion
                        : null,
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

        return view('bien.index', compact('bienes', 'tiposBien', 'total', 'estadosConservacion'));
    }

    /**
     * Guardar nuevo bien
     */
    public function store(BienRequest $request)
    {
        // ⭐ VALIDAR PERMISO
        if (!PermisosHelper::puedeRegistrarBien()) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para registrar bienes'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // 📸 Subir imagen a Cloudinary (optimización máxima de peso)
            if ($request->hasFile('foto_bien')) {
                $uploadedFile = Cloudinary::upload(
                    $request->file('foto_bien')->getRealPath(),
                    [
                        'folder' => 'bienes',
                        'transformation' => [
                            'width' => 600,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto:eco',
                            'fetch_format' => 'auto'
                        ]
                    ]
                );

                $data['foto_bien'] = $uploadedFile->getSecurePath();
                $data['public_id'] = $uploadedFile->getPublicId();
            }

            // ⭐⭐ Asignar estado administrativo AUTO → Activo
            $idActivo = \App\Models\EstadoBien::obtenerIdPorNombreNullable(\App\Models\EstadoBien::ACTIVO);
            $data['id_estado_bien'] = $idActivo;
            $data['activo'] = true;

            // ⭐⭐ GUARDAR QUIÉN REGISTRÓ EL BIEN ⭐⭐
            $data['registrado_por'] = Auth::id();

            $bien = Bien::create($data);
            $bien->load('documentoSustento', 'tipoBien', 'estadoConservacion');

            // ⭐⭐⭐ CREAR MOVIMIENTO AUTOMÁTICO DE ALTA ⭐⭐⭐
            // Buscar el tipo de movimiento ALTA (insensible a mayúsculas)
            $tipoAlta = TipoMvto::whereRaw("UPPER(TRIM(tipo_mvto)) = 'ALTA'")->first();

            // Si no existe, crearlo
            if (!$tipoAlta) {
                $tipoAlta = TipoMvto::create(['tipo_mvto' => 'ALTA']);
            }

            // ⭐ Obtener la ubicación de recepción inicial configurada
            $ubicacionRecepcion = \App\Models\Ubicacion::getUbicacionRecepcion();

            Movimiento::create([
                'idbien'                      => $bien->id_bien,
                'tipo_mvto'                   => $tipoAlta->id_tipo_mvto,
                'fecha_mvto'                  => $bien->fecha_registro ?? now(),
                'detalle_tecnico'             => 'REGISTRO DE ALTA — ' . $bien->denominacion_bien,
                'id_estado_conservacion_bien' => $bien->id_estado_conservacion,
                'idubicacion'                 => $ubicacionRecepcion?->id_ubicacion,
                'idusuario'                   => Auth::id(),
                'anulado'                     => false,
            ]);

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
        // ⭐ VALIDAR PERMISO
        if (!PermisosHelper::puedeEditarBien($bien)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este bien (pertenece a otra área)'
            ], 403);
        }

        $bien->load(['tipoBien', 'documentoSustento', 'estadoConservacion']);

        return response()->json([
            'id_bien'                => $bien->id_bien,
            'codigo_patrimonial'     => $bien->codigo_patrimonial,
            'denominacion_bien'      => $bien->denominacion_bien,
            'id_tipobien'            => $bien->id_tipobien,
            'id_documento'           => $bien->id_documento,
            'modelo_bien'            => $bien->modelo_bien,
            'marca_bien'             => $bien->marca_bien,
            'color_bien'             => $bien->color_bien,
            'dimensiones_bien'       => $bien->dimensiones_bien,
            'nserie_bien'            => $bien->nserie_bien,
            'fecha_registro'         => $bien->fecha_registro,
            'foto_bien'              => $bien->foto_bien,
            'NumDoc'                 => $bien->NumDoc,
            // ⭐ Condición física
            'id_estado_conservacion' => $bien->id_estado_conservacion,
        ]);
    }

    /**
     * Actualizar bien existente
     */
    public function update(BienRequest $request, Bien $bien)
    {
        // ⭐ VALIDAR PERMISO
        if (!PermisosHelper::puedeEditarBien($bien)) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar este bien (pertenece a otra área)'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $data = $request->validated();

            // 📸 Si hay nueva imagen (optimización máxima de peso)
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
                            'width' => 600,
                            'height' => 600,
                            'crop' => 'limit',
                            'quality' => 'auto:eco',
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
        // ⭐ SOLO ADMIN PUEDE ELIMINAR
        if (!PermisosHelper::puedeEliminarBien()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el ADMIN puede eliminar bienes'
            ], 403);
        }

        // ⭐ VALIDACIÓN PROFESIONAL: No eliminar si está en inventario activo
        if ($bien->estaEnInventarioActivo()) {
            $codigos = $bien->getInventariosActivos()->pluck('codigoinventario')->implode(', ');
            return response()->json([
                'success' => false,
                'message' => "No se puede eliminar el bien porque forma parte de un proceso de auditoría activo ({$codigos}). Cierre o anule el inventario primero."
            ], 422);
        }

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
                ->eliminados()
                ->when($search, function($query, $search) {
                    $query->where(function($q) use ($search) {
                        $searchLower = strtolower($search);
                        $q->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$searchLower}%"])
                          ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$searchLower}%"]);
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

            // ⭐ VALIDACIÓN PROFESIONAL: No restaurar si el bien está en un inventario de BAJA activo
            if ($bien->estaEnInventarioActivo()) {
                $inventariosBaja = $bien->getInventariosActivos()->filter(function($inv) {
                    return $inv->getRawOriginal('tipoinventario') === \App\Models\Inventario::TIPO_BAJA;
                });

                if ($inventariosBaja->isNotEmpty()) {
                    $codigos = $inventariosBaja->pluck('codigoinventario')->implode(', ');
                    return response()->json([
                        'success' => false,
                        'message' => "No se puede restaurar el bien porque forma parte de un proceso de auditoría de BAJA activo ({$codigos}). Debe anular o finalizar dicho inventario primero para garantizar la trazabilidad."
                    ], 422);
                }
            }

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
                ->where('anulado', false)
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
                    'tiene_movimientos' => false,
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
                    'ubicacion' => $ultimoMov->ubicacion->ambiente ?? 'Sin ubicación',
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
