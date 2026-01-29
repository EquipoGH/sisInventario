<?php

namespace App\Http\Controllers;

use App\Http\Requests\Modulo\StoreModuloRequest;
use App\Http\Requests\Modulo\UpdateModuloRequest;
use App\Models\Modulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ModuloController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Modulos');
    }
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 25, 50, 100], true) ? $perPage : 10;

        $q = trim((string) $request->get('q', ''));
        $search = trim((string) $request->get('search', $q));

        $orden = $request->get('orden', 'id');          // id | nombre | estado | etiqueta | color
        $direccion = $request->get('direccion', 'asc'); // asc | desc

        $query = Modulo::query();

        if ($search !== '') {
            if (method_exists(Modulo::class, 'scopeSearch')) $query->search($search);
            else {
                $query->where('nommodulo', 'like', "%{$search}%")
                      ->orWhere('idmodulo', 'like', "%{$search}%")
                      ->orWhere('etiqueta', 'like', "%{$search}%")
                      ->orWhere('color', 'like', "%{$search}%");
            }
        }

        $map = [
            'id' => 'idmodulo',
            'nombre' => 'nommodulo',
            'estado' => 'estadomodulo',
            'etiqueta' => 'etiqueta',
            'color' => 'color',
        ];
        $col = $map[$orden] ?? 'idmodulo';
        $dir = $direccion === 'desc' ? 'desc' : 'asc';
        $query->orderBy($col, $dir);

        $items = $query->paginate($perPage)->withQueryString(); // [web:25]

        if ($request->ajax() || $request->wantsJson()) { // [web:200]
            return response()->json([
                'data' => $items->items(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'resultados' => $items->count(),
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
            ]);
        }

        return view('modulo.index', [
            'items' => $items,
            'q' => $q,
            'perPage' => $perPage,
        ]);
    }

    public function store(StoreModuloRequest $request)
    {
        $modulo = Modulo::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrado',
                'data' => $modulo,
            ]);
        }

        return redirect()->route('modulo.index')->with('ok', 'Registrado');
    }

    public function edit(Modulo $modulo)
    {
        return response()->json([
            'idmodulo' => $modulo->idmodulo,
            'nommodulo' => $modulo->nommodulo,
            'estadomodulo' => $modulo->estadomodulo,
            'etiqueta' => $modulo->etiqueta,
            'color' => $modulo->color,
        ]);
    }

    public function update(UpdateModuloRequest $request, Modulo $modulo)
    {
        $modulo->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Actualizado',
            ]);
        }

        return redirect()->route('modulo.index')->with('ok', 'Actualizado');
    }

    public function destroy(Request $request, Modulo $modulo)
    {
        $modulo->delete();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Eliminado',
            ]);
        }

        return back()->with('ok', 'Eliminado');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            Modulo::whereIn('idmodulo', $data['ids'])->delete();
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros eliminados',
            ]);
        }

        return back()->with('ok', 'Registros eliminados');
    }
}
