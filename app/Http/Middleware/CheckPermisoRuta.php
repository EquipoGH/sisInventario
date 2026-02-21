<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermisoRuta
{
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();
        if (!$user) {
            return redirect()->route('login');
        }

        // nombre de ruta actual, ej: modulo.index
        $routeName = $request->route()->getName();

        // si la ruta no tiene nombre, la dejamos pasar (o bloquea, tú decides)
        if (!$routeName) {
            return $next($request);
        }

        // obtener IDs de perfil del usuario (ya lo usas en AppServiceProvider)
        $perfilIds = $user->perfiles()->pluck('perfil.idperfil');

        if ($perfilIds->isEmpty()) {
            return response()->view('errors.forbidden', [], 403);
        }

        // verificar si existe algún permiso asociado a esos perfiles
        // cuyo route_name coincida con la ruta actual
        $tienePermiso = \App\Models\PerfilModulo::query()
            ->whereIn('idperfil', $perfilIds->all())
            ->whereHas('permisos', function ($q) use ($routeName) {
                $q->where('route_name', $routeName)
                  ->where('estadopermiso', 'A');
            })
            ->exists();

        if (!$tienePermiso) {
            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
