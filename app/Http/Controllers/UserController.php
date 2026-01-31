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

    // NUEVO: filtros (vienen de tu vista)
    $rol    = trim((string) $request->get('rol', ''));      // ADMIN|USUARIO|INVITADO|''(todos)
    $estado = trim((string) $request->get('estado', ''));   // A|I|''
    $ultimo = trim((string) $request->get('ultimo', ''));   // hoy|7d|30d|nunca|''

    $orden = $request->get('orden', 'id');          // id | nombre | email | dni | rol | estado | ultimo
    $direccion = $request->get('direccion', 'asc'); // asc | desc

    $query = User::query();

    // Search (igual que tú)
    if ($search !== '') {
        $driver = $query->getConnection()->getDriverName();
        $op = $driver === 'pgsql' ? 'ilike' : 'like';

        $query->where(function ($qq) use ($search, $op) {
            $qq->where('name', $op, "%{$search}%")
               ->orWhere('email', $op, "%{$search}%")
               ->orWhere('dni_usuario', $op, "%{$search}%")
               ->orWhere('rol_usuario', $op, "%{$search}%")
               ->orWhere('estado_usuario', $op, "%{$search}%")
               ->orWhere('id', $op, "%{$search}%");
        });
    }

    // NUEVO: aplicar filtros (solo si vienen)
    $query->when($rol !== '', fn ($q) => $q->where('rol_usuario', $rol)); // when() condicional [web:109]
    $query->when($estado !== '', fn ($q) => $q->where('estado_usuario', $estado)); // [web:109]

    // Último acceso (si tu campo es datetime/timestamp)
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

    // Ordenamiento (igual que tú)
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
    $dir = $direccion === 'desc' ? 'desc' : 'asc';
    $query->orderBy($col, $dir);

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
        ]);
    }

    return view('user.index', [
        'items' => $items,
        'q' => $q,
        'perPage' => $perPage,
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
        // Aquí no necesitas mandar ultimo_acceso para editar (ya no se edita),
        // pero si lo quieres mostrar en algún lado, lo formateamos:
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'dni_usuario' => $user->dni_usuario,
            'rol_usuario' => $user->rol_usuario,
            'estado_usuario' => $user->estado_usuario,
            'ultimo_acceso' => $user->ultimo_acceso ? $user->ultimo_acceso->format('d/m/Y H:i') : null,
        ]);
    }

    public function update(UpdateUserRequest $request, User $user)
    {
        $data = $request->validated();

        // Si password viene vacío/null, no tocarlo (así no lo borra)
        if (!array_key_exists('password', $data) || $data['password'] === null || $data['password'] === '') {
            unset($data['password']);
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

    public function destroy(Request $request, User $user)
    {
        $user->delete();

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
            User::whereIn('id', $data['ids'])->delete();
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
