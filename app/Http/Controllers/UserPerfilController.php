<?php

namespace App\Http\Controllers;

use App\Http\Requests\User\SyncUserPerfilesRequest;
use App\Models\Perfil;
use App\Models\User;

class UserPerfilController extends Controller
{
    /**
     * Formulario para asignar perfiles a un usuario
     */
    public function edit(User $user)
{
    $user->load('perfiles');

    $perfiles = Perfil::query()
        ->select(['idperfil', 'nomperfil'])
        ->orderBy('nomperfil')
        ->get();

    if (request()->ajax()) {
        return view('user.perfiles.form', compact('user', 'perfiles'));
    }

    return view('user.perfiles', compact('user', 'perfiles'));
}


    /**
     * Guardar (sincronizar) perfiles del usuario
     */
    public function update(SyncUserPerfilesRequest $request, User $user)
{
    $ids = $request->input('perfiles', []);
    $user->perfiles()->sync($ids);

    if ($request->ajax()) {
        return response()->json(['success' => true]);
    }

    return redirect()->route('user.perfiles.edit', $user)->with('ok', 'Perfiles actualizados.');
}

}
