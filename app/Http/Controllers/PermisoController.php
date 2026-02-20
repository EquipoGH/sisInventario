<?php

namespace App\Http\Controllers;

use App\Http\Requests\Permiso\StorePermisoRequest;
use App\Http\Requests\Permiso\UpdatePermisoRequest;
use App\Models\Permiso;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

use App\Models\Perfil;
use App\Models\PerfilModulo;
use App\Models\ModuloPermiso;

class PermisoController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 25, 50, 100], true) ? $perPage : 10;

        $q = trim((string) $request->get('q', ''));
        $search = trim((string) $request->get('search', $q));

        // NUEVO: filtro por estado (A por defecto)
        $estado = strtoupper(trim((string) $request->get('estado', 'A')));
        if (!in_array($estado, ['A', 'I', 'ALL'], true)) {
            $estado = 'A';
        }

        $orden = (string) $request->get('orden', 'id'); // id | nombre | estado | route
        $direccion = strtolower((string) $request->get('direccion', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = Permiso::query();

        // Aplicar filtro estado (si no es ALL)
        if ($estado !== 'ALL') {
            $query->where('estadopermiso', $estado);
        }

        // Búsqueda
        if ($search !== '') {
            if (method_exists(Permiso::class, 'scopeSearch')) {
                $query->search($search);
            } else {
                $query->where(function ($qq) use ($search) {
                    $qq->where('nombpermiso', 'like', "%{$search}%")
                        ->orWhere('route_name', 'like', "%{$search}%")
                        ->orWhereRaw('CAST(idpermiso AS TEXT) LIKE ?', ["%{$search}%"]);
                });
            }
        }

        // Ordenamiento (whitelist)
        $map = [
            'id' => 'idpermiso',
            'nombre' => 'nombpermiso',
            'estado' => 'estadopermiso',
            'route' => 'route_name',
        ];

        $col = $map[$orden] ?? 'idpermiso';
        $query->orderBy($col, $direccion);

        $items = $query->paginate($perPage)->withQueryString();

        // JSON (AJAX)
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

        // Lista de rutas con nombre para el SELECT (route_name)
        $routeNames = collect(Route::getRoutes())
            ->map(fn ($r) => $r->getName())
            ->filter()
            ->unique()
            ->sort()
            ->values()
            ->all();

        return view('permiso.index', [
            'items' => $items,
            'q' => $q,
            'perPage' => $perPage,
            'routeNames' => $routeNames,
            'estado' => $estado,
        ]);
    }

    public function store(StorePermisoRequest $request)
    {
        $permiso = DB::transaction(function () use ($request) {
            // Crea permiso (sin idmodulo porque es pivote)
            $permiso = Permiso::create($request->safe()->except('idmodulo'));

            $idmodulo = $request->input('idmodulo');

            if ($idmodulo) {
                $perfilAdmin = Perfil::where('nomperfil', 'Admin')
                    ->orWhere('nomperfil', 'Administrador')
                    ->first();

                if ($perfilAdmin) {
                    $perfilModulo = PerfilModulo::firstOrCreate([
                        'idperfil' => $perfilAdmin->idperfil,
                        'idmodulo' => (int) $idmodulo,
                    ]);

                    ModuloPermiso::firstOrCreate([
                        'idperfilmodulo' => $perfilModulo->idperfilmodulo,
                        'idpermiso' => $permiso->idpermiso,
                    ]);
                }
            }

            return $permiso;
        });

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
        return response()->json([
            'idpermiso' => $permiso->idpermiso,
            'nombpermiso' => $permiso->nombpermiso,
            'estadopermiso' => $permiso->estadopermiso,
            'route_name' => $permiso->route_name,
        ]);
    }

    public function update(UpdatePermisoRequest $request, Permiso $permiso)
    {
        DB::transaction(function () use ($request, $permiso) {
            // Actualiza permiso (sin idmodulo)
            $permiso->update($request->safe()->except('idmodulo'));

            // Si NO quieres tocar pivot al editar, borra este bloque.
            $idmodulo = $request->input('idmodulo');

            if ($idmodulo) {
                $perfilAdmin = Perfil::where('nomperfil', 'Admin')
                    ->orWhere('nomperfil', 'Administrador')
                    ->first();

                if ($perfilAdmin) {
                    $perfilModulo = PerfilModulo::firstOrCreate([
                        'idperfil' => $perfilAdmin->idperfil,
                        'idmodulo' => (int) $idmodulo,
                    ]);

                    ModuloPermiso::firstOrCreate([
                        'idperfilmodulo' => $perfilModulo->idperfilmodulo,
                        'idpermiso' => $permiso->idpermiso,
                    ]);
                }
            }
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Actualizado',
            ]);
        }

        return redirect()->route('permiso.index')->with('ok', 'Actualizado');
    }

    // destroy = desactivar (eliminación lógica)
    public function destroy(Request $request, Permiso $permiso)
    {
        if (strtoupper((string) $permiso->estadopermiso) !== 'I') {
            $permiso->update(['estadopermiso' => 'I']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Desactivado',
            ]);
        }

        return back()->with('ok', 'Desactivado');
    }

    // restore = activar
    public function restore(Request $request, Permiso $permiso)
    {
        if (strtoupper((string) $permiso->estadopermiso) !== 'A') {
            $permiso->update(['estadopermiso' => 'A']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activado',
            ]);
        }

        return back()->with('ok', 'Activado');
    }

    // bulkDestroy = desactivar masivo (NO borra)
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            Permiso::whereIn('idpermiso', $data['ids'])
                ->update(['estadopermiso' => 'I']);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros desactivados',
            ]);
        }

        return back()->with('ok', 'Registros desactivados');
    }

    // bulkRestore = activar masivo
    public function bulkRestore(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            Permiso::whereIn('idpermiso', $data['ids'])
                ->update(['estadopermiso' => 'A']);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros activados',
            ]);
        }

        return back()->with('ok', 'Registros activados');
    }
}
