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

        $routeName = $request->route()->getName();
        if (!$routeName) {
            return $next($request);
        }

        $perfilIds = $user->perfiles()->pluck('perfil.idperfil');
        if ($perfilIds->isEmpty()) {
            return response()->view('errors.403', [], 403);
        }

        // ⭐ PostgreSQL: usa ~* en lugar de REGEXP
        $tienePermiso = \App\Models\PerfilModulo::query()
            ->whereIn('idperfil', $perfilIds->all())
            ->where(function($query) use ($routeName) {
                // 1️⃣ Permiso directo: route_name exacto
                $query->whereHas('permisos', function($p) use ($routeName) {
                    $p->where('route_name', $routeName)
                      ->where('estadopermiso', 'A');
                })
                // 2️⃣ O módulo padre con route_prefix que coincida (user.*, users.*, etc.)
                ->orWhereHas('modulo', function($m) use ($routeName) {
                    $m->where(function($mm) use ($routeName) {
                        // PostgreSQL: ~* para regex case-insensitive
                        $mm->whereRaw("route_prefix ~* ?", ['.*' . str_replace('*', '.*', $routeName)])
                           ->orWhere('route_prefix', $routeName)
                           ->orWhereRaw("'$routeName' ~* ?", [str_replace('*', '.*', $routeName)]);
                    });
                });
            })
            ->exists();

        if (!$tienePermiso) {
            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
