<?php

namespace App\Http\Controllers;

use App\Models\DocumentoSustento;
use App\Http\Requests\DocumentoSustentoRequest;
use Illuminate\Http\Request;

class DocumentoSustentoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search', '');
        $perPage = 10;

        // 🔥 PARÁMETROS DE ORDENAMIENTO
        $orden = $request->get('orden', 'id'); // Por defecto: ID
        $direccion = $request->get('direccion', 'desc'); // Por defecto: DESC

        // Validar columnas permitidas (seguridad)
        $columnasPermitidas = ['id', 'tipo', 'numero', 'fecha'];
        if (!in_array($orden, $columnasPermitidas)) {
            $orden = 'id';
        }

        // Validar dirección
        $direccion = in_array($direccion, ['asc', 'desc']) ? $direccion : 'desc';

        $query = DocumentoSustento::query();

        // Aplicar búsqueda
        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('id_documento', 'LIKE', "%{$search}%")
                  ->orWhere('tipo_documento', 'ILIKE', "%{$search}%")
                  ->orWhere('numero_documento', 'ILIKE', "%{$search}%")
                  ->orWhereRaw("TO_CHAR(fecha_documento, 'DD/MM/YYYY') LIKE ?", ["%{$search}%"]);
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
        \Log::info('Datos recibidos en store:', $request->all());

        try {
            $documento = DocumentoSustento::create($request->validated());

            \Log::info('DocumentoSustento creado:', $documento->toArray());

            return response()->json([
                'success' => true,
                'message' => 'Documento sustento creado exitosamente',
                'data' => $documento
            ]);
        } catch (\Exception $e) {
            \Log::error('Error al crear DocumentoSustento:', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error al crear el documento: ' . $e->getMessage()
            ], 500);
        }
    }

    public function edit(DocumentoSustento $documentoSustento)
    {
        return response()->json($documentoSustento);
    }

    public function update(DocumentoSustentoRequest $request, DocumentoSustento $documentoSustento)
    {
        try {
            $documentoSustento->update($request->validated());
            return response()->json([
                'success' => true,
                'message' => 'Documento sustento actualizado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(DocumentoSustento $documentoSustento)
    {
        try {
            $documentoSustento->delete();
            return response()->json([
                'success' => true,
                'message' => 'Documento sustento eliminado exitosamente'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }
}
