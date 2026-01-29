<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Symfony\Component\HttpFoundation\Response;

class PermisoMiddleware
{
    public function handle(Request $request, Closure $next, string $permiso): Response
    {
        // Si no tiene el permiso, Laravel devuelve 403 automáticamente
        Gate::authorize('permiso', $permiso);

        return $next($request);
    }
}
