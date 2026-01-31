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
    
    /**
     * Inyectar MovimientoService
     */
    public function __construct(MovimientoService $movimientoService)
    {
        // ✅ Doble capa: además de routes/web.php
        $this->movimientoService = $movimientoService;
    }

    /**
     * Listar bienes con búsqueda, ordenamiento dinámico y paginación
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;
        $total = Bien::count();

        // ⭐ INCLUIR RELACIONES
        $query = Bien::with(['tipoBien', 'documentoSustento']);

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

        // ⭐ PETICIÓN AJAX - FORMATEAR RESPUESTA
        if ($request->ajax()) {
            // Mapear bienes con relaciones explícitas
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
                    // ⭐ INCLUIR RELACIONES EXPLÍCITAMENTE
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

        // ⭐ NumDoc ahora viene del formulario (manual)
        // NO se sincroniza automáticamente

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

        // ⭐ CRÍTICO: Cargar relaciones antes de retornar
        $bien->load('documentoSustento', 'tipoBien');

        DB::commit();

        // ⭐ FORMATEAR RESPUESTA CON RELACIONES
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

        // ⭐ FORMATEAR RESPUESTA
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

        // ⭐ NumDoc ahora viene del formulario (manual)
        // NO se sincroniza automáticamente

        // 📸 Si hay nueva imagen
        if ($request->hasFile('foto_bien')) {
            // Eliminar imagen anterior
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

        // ⭐ CRÍTICO: Cargar relaciones antes de retornar
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
     * Eliminar bien
     */
    public function destroy(Bien $bien)
    {
        try {
            DB::beginTransaction();

            // 🗑️ Eliminar imagen de Cloudinary
            if ($bien->public_id) {
                try {
                    Cloudinary::destroy($bien->public_id);
                } catch (\Exception $e) {
                    Log::warning('Error al eliminar imagen: ' . $e->getMessage());
                }
            }

            $bien->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Bien eliminado exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
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
     * ⭐ Obtener documentos para SELECT en formulario
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
