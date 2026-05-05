<?php

namespace App\Http\Controllers;

use App\Models\DocumentoSustento;
use App\Http\Requests\DocumentoSustentoRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class DocumentoSustentoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // 🔥 PARÁMETROS DE ORDENAMIENTO
        $orden = $request->get('orden', 'id');
        $direccion = $request->get('direccion', 'desc');

        // Validar columnas permitidas (seguridad)
        $columnasPermitidas = ['id', 'tipo', 'numero', 'fecha'];
        if (!in_array($orden, $columnasPermitidas)) {
            $orden = 'id';
        }

        // Validar dirección
        $direccion = in_array($direccion, ['asc', 'desc']) ? $direccion : 'desc';

        // ⭐ MEJORADO: Incluir conteo de bienes
        $query = DocumentoSustento::withCount('bienes');

        // Aplicar búsqueda
        if (!empty($search)) {
            $searchLower = strtolower($search);
            $query->where(function($q) use ($search, $searchLower) {
                $q->where('id_documento', 'LIKE', "%{$search}%")
                  ->orWhereRaw('LOWER(tipo_documento) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw('LOWER(numero_documento) LIKE ?', ["%{$searchLower}%"])
                  ->orWhereRaw("DATE_FORMAT(fecha_documento, '%d/%m/%Y') LIKE ?", ["%{$search}%"]);
            });
        }

        // 🔥 APLICAR ORDENAMIENTO
        switch ($orden) {
            case 'id':
                $query->orderBy('id_documento', $direccion);
                break;
            case 'tipo':
                $query->orderBy('tipo_documento', $direccion);
                break;
            case 'numero':
                $query->orderBy('numero_documento', $direccion);
                break;
            case 'fecha':
                $query->orderBy('fecha_documento', $direccion);
                break;
            default:
                $query->orderBy('id_documento', 'desc');
        }

        $documentos = $query->paginate($perPage);
        $total = DocumentoSustento::count();

        // Si es AJAX, devolver JSON
        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $documentos->items(),
                'total' => $total,
                'resultados' => $documentos->total(),
                'current_page' => $documentos->currentPage(),
                'last_page' => $documentos->lastPage(),
                'per_page' => $documentos->perPage(),
                'from' => $documentos->firstItem(),
                'to' => $documentos->lastItem()
            ]);
        }

        return view('documento_sustento.index', compact('documentos', 'total'));
    }

    public function store(DocumentoSustentoRequest $request)
    {
        // ⭐ VALIDAR PERMISO
        if (!auth()->user()->esAdmin() && strtoupper(auth()->user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para registrar documentos'
            ], 403);
        }

        Log::info('Datos recibidos en store:', $request->all());

        try {
            DB::beginTransaction();

            $documento = DocumentoSustento::create($request->validated());

            DB::commit();

            Log::info('DocumentoSustento creado:', $documento->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Documento sustento creado exitosamente',
                'data' => $documento
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al crear DocumentoSustento:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(DocumentoSustento $documentoSustento)
    {
        // ⭐ VALIDAR PERMISO
        if (!auth()->user()->esAdmin() && strtoupper(auth()->user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para editar documentos'
            ], 403);
        }

        // ⭐ MEJORADO: Incluir cantidad de bienes
        $documentoSustento->loadCount('bienes');
        return response()->json($documentoSustento);
    }

    public function update(DocumentoSustentoRequest $request, DocumentoSustento $documentoSustento)
    {
        // ⭐ VALIDAR PERMISO
        if (!auth()->user()->esAdmin() && strtoupper(auth()->user()->rol_usuario) !== 'INFORMATICA') {
            return response()->json([
                'success' => false,
                'message' => 'No tienes permiso para actualizar documentos'
            ], 403);
        }

        try {
            DB::beginTransaction();

            $documentoSustento->update($request->validated());

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento sustento actualizado exitosamente',
                'data' => $documentoSustento
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al actualizar DocumentoSustento:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(DocumentoSustento $documentoSustento)
    {
        // ⭐ SOLO ADMIN PUEDE ELIMINAR
        if (!auth()->user()->esAdmin()) {
            return response()->json([
                'success' => false,
                'message' => 'Solo el administrador puede eliminar documentos'
            ], 403);
        }

        try {
            // ⭐ CRÍTICO: Validar que no tenga bienes asociados
            if ($documentoSustento->tieneBienes()) {
                $cantidad = $documentoSustento->cantidadBienes();
                return response()->json([
                    'success' => false,
                    'message' => "No se puede eliminar. Tiene {$cantidad} bien(es) asociado(s).",
                    'bienes_count' => $cantidad
                ], 409); // 409 Conflict
            }

            DB::beginTransaction();

            $documentoSustento->delete();

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Documento sustento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al eliminar DocumentoSustento:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    // ⭐ NUEVO: Método para obtener documentos (usado en BienController)
    public function obtenerDocumentos()
    {
        try {
            $documentos = DocumentoSustento::select(
                    'id_documento',
                    'tipo_documento',
                    'numero_documento',
                    'fecha_documento'
                )
                ->orderBy('fecha_documento', 'desc')
                ->get()
                ->map(function($doc) {
                    return [
                        'id' => $doc->id_documento,
                        'text' => "{$doc->tipo_documento} - {$doc->numero_documento} ({$doc->fecha_formateada})"
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

    // ⭐ NUEVO: Validar número de documento único
    public function verificarNumero(Request $request)
    {
        $numero = $request->input('numero');
        $id = $request->input('id'); // null si es creación, id_documento si es edición

        $existe = DocumentoSustento::where('numero_documento', $numero)
            ->when($id, function($query) use ($id) {
                return $query->where('id_documento', '!=', $id);
            })
            ->exists();

        return response()->json([
            'existe' => $existe,
            'disponible' => !$existe
        ]);
    }

    // ⭐ NUEVO: Obtener bienes asociados a un documento
    public function bienes(DocumentoSustento $documentoSustento)
    {
        try {
            $bienes = $documentoSustento->bienesConDetalles();

            return response()->json([
                'success' => true,
                'data' => $bienes,
                'total' => $bienes->count()
            ]);
        } catch (\Exception $e) {
            Log::error('Error al obtener bienes del documento:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al obtener bienes'
            ], 500);
        }
    }

    // ⭐ NUEVO: Desvincular bienes (establecer id_documento a NULL)
    public function desvincularBienes(Request $request, DocumentoSustento $documentoSustento)
    {
        try {
            DB::beginTransaction();

            $count = $documentoSustento->bienes()->update([
                'id_documento' => null,
                'NumDoc' => null
            ]);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => "{$count} bien(es) desvinculado(s) exitosamente",
                'count' => $count
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error al desvincular bienes:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al desvincular bienes'
            ], 500);
        }
    }
}
