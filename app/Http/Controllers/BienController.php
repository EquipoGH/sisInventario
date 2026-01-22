<?php

namespace App\Http\Controllers;

use App\Models\Bien;
use App\Models\TipoBien;
use App\Http\Requests\BienRequest;
use App\Services\MovimientoService;
use Illuminate\Http\Request;
use CloudinaryLabs\CloudinaryLaravel\Facades\Cloudinary;
use Illuminate\Support\Facades\Log;

class BienController extends Controller
{
    protected $movimientoService;

    /**
     * Inyectar MovimientoService
     */
    public function __construct(MovimientoService $movimientoService)
    {
        $this->movimientoService = $movimientoService;
    }

    /**
     * Listar bienes con búsqueda, ordenamiento dinámico y paginación
     * SOLO SE ORDENA POR: Código, Denominación y Fecha (las columnas críticas)
     */
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // Total de registros sin filtros
        $total = Bien::count();

        $query = Bien::with('tipoBien');

        // 🔍 BÚSQUEDA AVANZADA
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_bien', 'LIKE', "%{$search}%")
                  ->orWhere('codigo_patrimonial', 'ILIKE', "%{$search}%")
                  ->orWhere('denominacion_bien', 'ILIKE', "%{$search}%")
                  ->orWhere('marca_bien', 'ILIKE', "%{$search}%")
                  ->orWhere('modelo_bien', 'ILIKE', "%{$search}%")
                  ->orWhereHas('tipoBien', function($q) use ($search) {
                      $q->where('nombre_tipo', 'ILIKE', "%{$search}%");
                  });
            });
        }

        // 📊 ORDENAMIENTO DINÁMICO (SOLO COLUMNAS CRÍTICAS)
        $columna = $request->get('orden', 'created_at');
        $direccion = $request->get('direccion', 'desc');

        // Mapeo de columnas (SOLO LAS 3 QUE TIENEN SENTIDO)
        $columnasPermitidas = [
            'codigo' => 'codigo_patrimonial',      // Para gestión administrativa
            'denominacion' => 'denominacion_bien', // Para búsqueda visual A-Z
            'fecha' => 'created_at'                // Para control temporal (PREDETERMINADO)
        ];

        // Validar que la columna existe
        if (array_key_exists($columna, $columnasPermitidas)) {
            $columnaReal = $columnasPermitidas[$columna];
        } else {
            $columnaReal = 'created_at'; // Fallback: siempre ordena por fecha
        }

        // Validar dirección (solo asc o desc)
        $direccion = in_array(strtolower($direccion), ['asc', 'desc'])
            ? strtolower($direccion)
            : 'desc';

        // Aplicar ordenamiento
        $query->orderBy($columnaReal, $direccion);

        // 📄 PAGINACIÓN
        $bienes = $query->paginate($perPage);
        $tiposBien = TipoBien::orderBy('nombre_tipo')->get();

        // 📤 RESPUESTA AJAX
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $bienes->items(),
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
     * ✅ EL OBSERVER SE ENCARGA DE CREAR EL MOVIMIENTO AUTOMÁTICAMENTE
     */
    public function store(BienRequest $request)
    {
        try {
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
            }

            // Crear bien (el Observer registrará el movimiento automáticamente)
            $bien = Bien::create($data);

            return response()->json([
                'success' => true,
                'message' => 'Bien registrado exitosamente (movimiento creado automáticamente)',
                'data' => $bien->load('tipoBien')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al crear bien: ' . $e->getMessage());

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
        $bien->load('tipoBien');
        return response()->json($bien);
    }

    /**
     * Actualizar bien existente
     * ✅ EL OBSERVER SE ENCARGA DE CREAR EL MOVIMIENTO AUTOMÁTICAMENTE
     */
    public function update(BienRequest $request, Bien $bien)
    {
        try {
            $data = $request->validated();

            // 📸 Si hay nueva imagen
            if ($request->hasFile('foto_bien')) {
                // Eliminar imagen anterior
                if ($bien->foto_bien) {
                    $this->deleteCloudinaryImage($bien->foto_bien);
                }

                // Subir nueva imagen
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
            }

            // Actualizar bien (el Observer registrará el movimiento si hay cambios relevantes)
            $bien->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Bien actualizado exitosamente (movimiento registrado si hubo cambios)',
                'data' => $bien->load('tipoBien')
            ]);
        } catch (\Exception $e) {
            Log::error('Error al actualizar bien: ' . $e->getMessage());

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
            // Verificar si tiene movimientos
            if ($bien->tieneMovimientos()) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se puede eliminar. El bien tiene movimientos registrados.'
                ], 400);
            }

            // 🗑️ Eliminar imagen de Cloudinary
            if ($bien->foto_bien) {
                $this->deleteCloudinaryImage($bien->foto_bien);
            }

            $bien->delete();

            return response()->json([
                'success' => true,
                'message' => 'Bien eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Error al eliminar bien: ' . $e->getMessage());

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
            'mensaje' => $existe ? 'Este código ya está en uso' : 'Código disponible'
        ]);
    }

    /**
     * Ver historial de movimientos de un bien
     */
    public function historialMovimientos(Bien $bien)
    {
        $movimientos = $bien->historialMovimientos();

        return response()->json([
            'success' => true,
            'data' => $movimientos
        ]);
    }

    /**
     * Eliminar imagen de Cloudinary
     */
    private function deleteCloudinaryImage($imageUrl)
    {
        try {
            $parts = explode('/', $imageUrl);
            $filename = end($parts);
            $publicId = 'bienes/' . pathinfo($filename, PATHINFO_FILENAME);

            Cloudinary::destroy($publicId);

            Log::info('Imagen eliminada de Cloudinary: ' . $publicId);
        } catch (\Exception $e) {
            Log::warning('No se pudo eliminar imagen de Cloudinary: ' . $e->getMessage());
        }
    }
}
