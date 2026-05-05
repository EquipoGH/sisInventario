<?php

namespace App\Http\Controllers;

use App\Http\Requests\Modulo\StoreModuloRequest;
use App\Http\Requests\Modulo\UpdateModuloRequest;
use App\Models\Modulo;
use App\Models\Perfil;
use App\Models\PerfilModulo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

class ModuloController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 25, 50, 100], true) ? $perPage : 10;

        $q = trim((string) $request->get('q', ''));
        $search = trim((string) $request->get('search', $q));

        $estado = strtoupper(trim((string) $request->get('estado', 'A')));
        if (!in_array($estado, ['A', 'I', 'ALL'], true)) $estado = 'A';

        $orden = (string) $request->get('orden', 'id');
        $direccion = strtolower((string) $request->get('direccion', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = Modulo::query();

        if ($estado !== 'ALL') {
            $query->where('estadomodulo', $estado);
        }

        if ($search !== '') {
            $query->search($search); // SIEMPRE usa scopeSearch (portable)
        }

        $map = [
            'id' => 'idmodulo',
            'nombre' => 'nommodulo',
            'estado' => 'estadomodulo',
            'etiqueta' => 'etiqueta',
            'color' => 'color',
            'icono' => 'icono',
            'route_prefix' => 'route_prefix',
        ];

        $col = $map[$orden] ?? 'idmodulo';
        $query->orderBy($col, $direccion);

        $items = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'data' => $items->items(),
                'from' => $items->firstItem(),
                'to' => $items->lastItem(),
                'resultados' => $items->count(),
                'total' => $items->total(),
                'current_page' => $items->currentPage(),
                'last_page' => $items->lastPage(),
                'per_page' => $items->perPage(),
                'estado' => $estado,
            ]);
        }

        $routePrefixes = collect(Route::getRoutes())
            ->map(fn ($r) => $r->getName())
            ->filter()
            ->unique()
            ->map(function ($name) {
                $first = explode('.', $name)[0] ?? null;
                return $first ? ($first . '.*') : null;
            })
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('modulo.index', [
            'items' => $items,
            'q' => $q,
            'perPage' => $perPage,
            'routePrefixes' => $routePrefixes,
            'estado' => $estado,
        ]);
    }

    public function store(StoreModuloRequest $request)
    {
        $modulo = DB::transaction(function () use ($request) {
            $modulo = Modulo::create($request->validated());

            $perfilAdmin = Perfil::where('nomperfil', 'Admin')
                ->orWhere('nomperfil', 'Administrador')
                ->first();

            if ($perfilAdmin) {
                PerfilModulo::firstOrCreate([
                    'idperfil' => $perfilAdmin->idperfil,
                    'idmodulo' => $modulo->idmodulo,
                ]);
            }

            return $modulo;
        });

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
            'icono' => $modulo->icono,
            'route_prefix' => $modulo->route_prefix,
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
        if (strtoupper((string) $modulo->estadomodulo) !== 'I') {
            $modulo->update(['estadomodulo' => 'I']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Inactivado',
            ]);
        }

        return back()->with('ok', 'Inactivado');
    }

    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            Modulo::whereIn('idmodulo', $data['ids'])
                ->update(['estadomodulo' => 'I']);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros inactivados',
            ]);
        }

        return back()->with('ok', 'Registros inactivados');
    }

    public function restore(Request $request, Modulo $modulo)
    {
        if (strtoupper((string) $modulo->estadomodulo) !== 'A') {
            $modulo->update(['estadomodulo' => 'A']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Reactivado',
            ]);
        }

        return back()->with('ok', 'Reactivado');
    }

    public function bulkRestore(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            Modulo::whereIn('idmodulo', $data['ids'])
                ->update(['estadomodulo' => 'A']);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros reactivados',
            ]);
        }

        return back()->with('ok', 'Registros reactivados');
    }
}
