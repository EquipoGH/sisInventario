<?php

namespace App\Http\Controllers;

use App\Http\Requests\Perfil\SyncPerfilModulosRequest;
use App\Models\Modulo;
use App\Models\Perfil;
use Illuminate\Http\Request;

class PerfilModuloController extends Controller
{
    /**
     * Formulario (modal o página) para asignar módulos a un perfil
     */
    public function edit(Perfil $perfil)
{
    $perfil->load('modulos');

    $modulos = Modulo::query()
        ->select(['idmodulo', 'nommodulo', 'etiqueta', 'color', 'estadomodulo'])
        ->orderBy('etiqueta')
        ->orderBy('nommodulo')
        ->get()
        ->groupBy(fn ($m) => $m->etiqueta ?: 'SIN ETIQUETA');

    $asignados = $perfil->modulos->pluck('idmodulo')->all();

    // ESTE ES EL QUE TE FALTA:
    $mapPerfilModulo = $perfil->modulos
        ->mapWithKeys(fn($m) => [$m->idmodulo => $m->pivot->idperfilmodulo])
        ->toArray();

    if (request()->ajax()) {
        return view('perfil.modulos.form', compact('perfil', 'modulos', 'asignados', 'mapPerfilModulo'));
    }

    return view('perfil.modulos', compact('perfil', 'modulos', 'asignados', 'mapPerfilModulo'));
}


    /**
     * Guardar (sync) módulos del perfil
     */
    public function update(SyncPerfilModulosRequest $request, Perfil $perfil)
    {
        $ids = $request->input('modulos', []);
        $perfil->modulos()->sync($ids);

        if ($request->ajax()) {
            return response()->json(['success' => true]);
        }

        return redirect()
            ->route('perfil.modulos.edit', $perfil)
            ->with('ok', 'Módulos actualizados.');
    }
}
