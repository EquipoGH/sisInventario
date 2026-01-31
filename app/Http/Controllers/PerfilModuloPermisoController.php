<?php

namespace App\Http\Controllers;

use App\Http\Requests\PerfilModulo\GetPerfilModuloPermisosRequest;
use App\Http\Requests\PerfilModulo\SyncPerfilModuloPermisosRequest;
use App\Models\PerfilModulo;
use App\Models\Permiso;
use Illuminate\Http\Request;

class PerfilModuloPermisoController extends Controller
{
    public function edit(GetPerfilModuloPermisosRequest $request, PerfilModulo $perfilModulo)
    {
        $perfilModulo->load(['perfil', 'modulo', 'permisos']);

        $permisos = Permiso::query()
            ->orderBy('nombpermiso') // ajusta al campo real
            ->get();

        $asignados = $perfilModulo->permisos->pluck('idpermiso')->all();

        if ($request->expectsJson() || $request->ajax()) {
            return view('perfil_modulo.permisos.form', compact('perfilModulo', 'permisos', 'asignados'));
        }

        return view('perfil_modulo.permisos', compact('perfilModulo', 'permisos', 'asignados'));
    }

    public function update(SyncPerfilModuloPermisosRequest $request, PerfilModulo $perfilModulo)
    {
        $ids = $request->input('permisos', []);
        $perfilModulo->permisos()->sync($ids);

        if ($request->expectsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('ok', 'Permisos actualizados.');
    }

    public function index(Request $request, PerfilModulo $perfilModulo)
    {
        $perfilModulo->load('permisos');

        return response()->json([
            'idperfilmodulo' => $perfilModulo->idperfilmodulo,
            'permisos' => $perfilModulo->permisos->pluck('idpermiso')->values()->all(),
        ]);
    }
}
