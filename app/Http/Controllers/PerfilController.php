<?php

namespace App\Http\Controllers;

use App\Http\Requests\Perfil\StorePerfilRequest;
use App\Http\Requests\Perfil\UpdatePerfilRequest;
use App\Models\Perfil;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PerfilController extends Controller
{
    public function __construct()
    {
        $this->middleware('permiso:Perfiles');
    }
    public function index(Request $request)
    {
        // ====== Parámetros ======
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 25, 50, 100], true) ? $perPage : 10;

        // Para vista normal usas q, para ajax usas search (como áreas)
        $q = trim((string) $request->get('q', ''));
        $search = trim((string) $request->get('search', $q));

        $orden = $request->get('orden', 'id');        // id | nombre
        $direccion = $request->get('direccion', 'asc'); // asc | desc

        // ====== Query base ======
        $query = Perfil::query();

        if ($search !== '') {
            // Si tienes scopeSearch úsalo, si no, usa where directo:
            if (method_exists(Perfil::class, 'scopeSearch')) {
                $query->search($search);
            } else {
                $query->where('nomperfil', 'like', "%{$search}%")
                      ->orWhere('idperfil', 'like', "%{$search}%");
            }
        }

        // Ordenamiento
        $map = [
            'id' => 'idperfil',
            'nombre' => 'nomperfil',
        ];
        $col = $map[$orden] ?? 'idperfil';
        $dir = $direccion === 'desc' ? 'desc' : 'asc';
        $query->orderBy($col, $dir);

        $items = $query->paginate($perPage)->withQueryString();

        // ====== Si es AJAX => JSON (para el JS estilo Áreas) ======
        if ($request->ajax()) {
            return response()->json([
                'data' => $items->items(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'resultados' => $items->count(),   // resultados en esta página
                'total' => $items->total(),        // total general
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
            ]);
        }

        // ====== Vista normal ======
        return view('perfil.index', [
            'items' => $items,
            'q' => $q,
            'perPage' => $perPage,
        ]);
    }

    public function store(StorePerfilRequest $request)
    {
        $perfil = Perfil::create($request->validated());

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrado',
                'data' => $perfil,
            ]);
        }

        return redirect()->route('perfil.index')->with('ok', 'Registrado');
    }

    // IMPORTANTE: para tu modal (AJAX) debe devolver JSON, no vista.
    public function edit(Perfil $perfil)
    {
        return response()->json([
            'idperfil' => $perfil->idperfil,
            'nomperfil' => $perfil->nomperfil,
        ]);
    }

    public function update(UpdatePerfilRequest $request, Perfil $perfil)
    {
        $perfil->update($request->validated());

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Actualizado',
            ]);
        }

        return redirect()->route('perfil.index')->with('ok', 'Actualizado');
    }

    public function destroy(Perfil $perfil)
    {
        $perfil->delete();

        if (request()->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Eliminado',
            ]);
        }

        return back()->with('ok', 'Eliminado');
    }

    // Mantengo tu bulkDestroy (por si lo quieres usar en otro lado)
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            Perfil::whereIn('idperfil', $data['ids'])->delete();
        });

        if ($request->ajax()) {
            return response()->json(['success' => true, 'message' => 'Registros eliminados']);
        }

        return back()->with('ok', 'Registros eliminados');
    }
}
