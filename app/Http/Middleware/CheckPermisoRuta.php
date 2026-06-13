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

        // Obtener los módulos y permisos del usuario en memoria para evaluación agnóstica de DB
        $perfilModulos = \App\Models\PerfilModulo::query()
            ->whereIn('idperfil', $perfilIds->all())
            ->with(['modulo', 'permisos'])
            ->get();

        $tienePermiso = $perfilModulos->contains(function ($pm) use ($routeName) {
            // Mapa de excepciones para rutas hijas que no comparten el mismo prefijo estricto de su padre
            $routePermissionMap = [
                'users.perfiles.edit' => 'user.index',
                'users.perfiles.update' => 'user.index',
                'perfil-modulo.permisos.edit' => 'perfil.index',
                'perfil-modulo.permisos.update' => 'perfil.index',
                'perfil-modulo.permisos.index' => 'perfil.index',
                // Rutas de API auxiliares - accesibles para cualquier usuario autenticado
                'areas.getResponsable' => 'inventario.index',
                'areas.getUbicaciones' => 'inventario.index',
            ];

            foreach ($pm->permisos as $permiso) {
                if ($permiso->estadopermiso !== 'A' || empty($permiso->route_name)) {
                    continue;
                }

                // 1. Coincidencia exacta
                if ($permiso->route_name === $routeName) {
                    return true;
                }

                // 2. Coincidencia por excepciones de mapa
                if (isset($routePermissionMap[$routeName]) && $routePermissionMap[$routeName] === $permiso->route_name) {
                    return true;
                }

                // 3. Coincidencia por prefijo derivado del index (ej: bien.index -> permite bien.*)
                if (str_ends_with($permiso->route_name, '.index')) {
                    $prefix = str_replace('.index', '.', $permiso->route_name); // "bien."
                    if (\Illuminate\Support\Str::startsWith($routeName, $prefix)) {
                        return true;
                    }
                }

                // 4. Coincidencia por prefijo directo (ej: configuracion.institucion -> permite configuracion.institucion.*)
                $directPrefix = $permiso->route_name . '.';
                if (\Illuminate\Support\Str::startsWith($routeName, $directPrefix)) {
                    return true;
                }
            }

            return false;
        });

        if (!$tienePermiso) {
            return response()->view('errors.403', [], 403);
        }

        return $next($request);
    }
}
