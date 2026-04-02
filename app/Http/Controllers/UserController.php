<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\StoreUserRequest;
use App\Http\Requests\User\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 10);
        $perPage = in_array($perPage, [5, 10, 20, 25, 50, 100], true) ? $perPage : 10;

        $q = trim((string) $request->get('q', ''));
        $search = trim((string) $request->get('search', $q));

        // Filtros
        $rol = trim((string) $request->get('rol', '')); // ADMIN|USUARIO|INVITADO|''
        $estado = strtoupper(trim((string) $request->get('estado', 'A'))); // A|I|ALL  (A por defecto)
        $ultimo = trim((string) $request->get('ultimo', '')); // hoy|7d|30d|nunca|''

        if (!in_array($estado, ['A', 'I', 'ALL'], true)) {
            $estado = 'A';
        }

        $orden = (string) $request->get('orden', 'id');          // id | nombre | email | dni | rol | estado | ultimo
        $direccion = strtolower((string) $request->get('direccion', 'asc')) === 'desc' ? 'desc' : 'asc';

        $query = User::query();

        // Search
        if ($search !== '') {
            $driver = $query->getConnection()->getDriverName();
            $op = $driver === 'pgsql' ? 'ilike' : 'like';

            $query->where(function ($qq) use ($search, $op) {
                $qq->where('name', $op, "%{$search}%")
                    ->orWhere('email', $op, "%{$search}%")
                    ->orWhere('dni_usuario', $op, "%{$search}%")
                    ->orWhere('rol_usuario', $op, "%{$search}%")
                    ->orWhere('estado_usuario', $op, "%{$search}%")
                    ->orWhereRaw('CAST(id AS TEXT) ' . ($op === 'ilike' ? 'ILIKE' : 'LIKE') . ' ?', ["%{$search}%"]);
            });
        }

        // Aplicar filtros
        $query->when($rol !== '', fn ($q) => $q->where('rol_usuario', $rol));

        // Estado: A/I/ALL
        if ($estado !== 'ALL') {
            $query->where('estado_usuario', $estado);
        }

        // Último acceso
        $query->when($ultimo !== '', function ($q) use ($ultimo) {
            if ($ultimo === 'nunca') {
                $q->whereNull('ultimo_acceso');
                return;
            }

            if ($ultimo === 'hoy') {
                $q->whereDate('ultimo_acceso', now()->toDateString());
                return;
            }

            if ($ultimo === '7d') {
                $q->where('ultimo_acceso', '>=', now()->subDays(7));
                return;
            }

            if ($ultimo === '30d') {
                $q->where('ultimo_acceso', '>=', now()->subDays(30));
                return;
            }
        });

        // Ordenamiento (whitelist)
        $map = [
            'id' => 'id',
            'nombre' => 'name',
            'email' => 'email',
            'dni' => 'dni_usuario',
            'rol' => 'rol_usuario',
            'estado' => 'estado_usuario',
            'ultimo' => 'ultimo_acceso',
        ];

        $col = $map[$orden] ?? 'id';
        $query->orderBy($col, $direccion);

        $items = $query->paginate($perPage)->withQueryString();

        if ($request->ajax() || $request->wantsJson()) {
            $data = collect($items->items())->map(function (User $u) {
                return [
                    'id' => $u->id,
                    'name' => $u->name,
                    'email' => $u->email,
                    'dni_usuario' => $u->dni_usuario,
                    'rol_usuario' => $u->rol_usuario,
                    'estado_usuario' => $u->estado_usuario,
                    'id_responsable' => $u->id_responsable,
                    'ultimo_acceso' => $u->ultimo_acceso ? $u->ultimo_acceso->format('d/m/Y H:i') : null,
                ];
            })->values();

            return response()->json([
                'data' => $data,
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

        return view('user.index', [
            'items' => $items,
            'q' => $q,
            'perPage' => $perPage,
            'estado' => $estado,
            'rol' => $rol,
            'ultimo' => $ultimo,
        ]);
    }

    public function store(StoreUserRequest $request)
    {
        $data = $request->validated();
        $user = User::create($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registrado',
                'data' => $user,
            ]);
        }

        return redirect()->route('user.index')->with('ok', 'Registrado');
    }

    public function edit(User $user)
    {
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dni_usuario' => $user->dni_usuario,
            'rol_usuario' => $user->rol_usuario,
            'estado_usuario' => $user->estado_usuario,
            'id_responsable' => $user->id_responsable,
            'ultimo_acceso' => $user->ultimo_acceso ? $user->ultimo_acceso->format('d/m/Y H:i') : null,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Si password viene vacío/null, no tocarlo
        if (!array_key_exists('password', $data) || $data['password'] === null || $data['password'] === '') {
            unset($data['password']);
        }

        // Si password_movil viene vacío/null, no tocarlo
        if (!array_key_exists('password_movil', $data) || $data['password_movil'] === null || $data['password_movil'] === '') {
            unset($data['password_movil']);
        }

        $user->update($data);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Actualizado',
            ]);
        }

        return redirect()->route('user.index')->with('ok', 'Actualizado');
    }

    // Desactivar (lógico) - NO borrar
    public function destroy(Request $request, User $user)
    {
        if (strtoupper((string) $user->estado_usuario) !== 'I') {
            $user->update(['estado_usuario' => 'I']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Desactivado',
            ]);
        }

        return back()->with('ok', 'Desactivado');
    }

    // Activar (uno)
    public function restore(Request $request, User $user)
    {
        if (strtoupper((string) $user->estado_usuario) !== 'A') {
            $user->update(['estado_usuario' => 'A']);
        }

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Activado',
            ]);
        }

        return back()->with('ok', 'Activado');
    }

    // Desactivar masivo (NO borra)
    public function bulkDestroy(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            User::whereIn('id', $data['ids'])
                ->update(['estado_usuario' => 'I']);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros desactivados',
            ]);
        }

        return back()->with('ok', 'Registros desactivados');
    }

    // Activar masivo
    public function bulkRestore(Request $request)
    {
        $data = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer'],
        ]);

        DB::transaction(function () use ($data) {
            User::whereIn('id', $data['ids'])
                ->update(['estado_usuario' => 'A']);
        });

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Registros activados',
            ]);
        }

        return back()->with('ok', 'Registros activados');
    }

    /**
     * Obtener lista de responsables para SELECT
     */
    public function obtenerResponsables()
    {
        try {
            $responsables = \App\Models\Responsable::select('dni_responsable', 'nombre_responsable', 'apellidos_responsable')
                ->orderBy('nombre_responsable')
                ->get()
                ->map(function ($resp) {
                    return [
                        'id' => $resp->dni_responsable,
                        'text' => $resp->nombre_completo,
                    ];
                });

            return response()->json($responsables);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener responsables'
            ], 500);
        }
    }
}
