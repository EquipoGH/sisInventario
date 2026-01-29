<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permiso\StorePermisoRequest;
use App\Http\Requests\Permiso\UpdatePermisoRequest;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PermisoController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Permisos');
    }
    public function index(Request $request)
    {
        // ====== Parámetros ======
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 25, 50, 100], true) ? $perPage : 10;

        // Vista: q | AJAX: search
        $q = trim((string) $request->get('q', ''));
        $search = trim((string) $request->get('search', $q));

        $orden = $request->get('orden', 'id');            // id | nombre | estado
        $direccion = $request->get('direccion', 'asc');   // asc | desc

        // ====== Query ======
        $query = Permiso::query();

        if ($search !== '') {
            // Usa scopeSearch si existe, si no where directo
            if (method_exists(Permiso::class, 'scopeSearch')) {
                $query->search($search);
            } else {
                $query->where('nombpermiso', 'like', "%{$search}%")
                      ->orWhere('idpermiso', 'like', "%{$search}%");
            }
        }

        // Ordenamiento (columnas reales de tu migración)
        $map = [
            'id' => 'idpermiso',
            'nombre' => 'nombpermiso',
            'estado' => 'estadopermiso',
        ];
        $col = $map[$orden] ?? 'idpermiso';
        $dir = $direccion === 'desc' ? 'desc' : 'asc';
        $query->orderBy($col, $dir);

        $items = $query->paginate($perPage)->withQueryString(); // [web:25]

        // ====== AJAX/JSON ======
        if ($request->ajax() || $request->wantsJson()) { // wantsJson revisa header Accept [web:169]
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

        // ====== Vista normal ======
        return view('permiso.index', [
            'items' => $items,
            'q' => $q,
            'perPage' => $perPage,
        ]);
    }

    public function store(StorePermisoRequest $request)
    {
        $permiso = Permiso::create($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrado',
                'data' => $permiso,
            ]);
        }

        return redirect()->route('permiso.index')->with('ok', 'Registrado');
    }

    public function edit(Permiso $permiso)
    {
        // Para modal AJAX
        return response()->json([
            'idpermiso' => $permiso->idpermiso,
            'nombpermiso' => $permiso->nombpermiso,
            'estadopermiso' => $permiso->estadopermiso,
        ]);
    }

    public function update(UpdatePermisoRequest $request, Permiso $permiso)
    {
        $permiso->update($request->validated());

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Actualizado',
            ]);
        }

        return redirect()->route('permiso.index')->with('ok', 'Actualizado');
    }

    public function destroy(Request $request, Permiso $permiso)
    {
        $permiso->delete();

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
            Permiso::whereIn('idpermiso', $data['ids'])->delete();
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
