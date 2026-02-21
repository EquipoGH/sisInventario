<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProfilePhotoController extends Controller
{
    public function __construct()
    {
        // Asegura que solo usuarios autenticados puedan subir foto
        $this->middleware('auth');
    }

    public function update(Request $request)
    {
        $validated = $request->validate([
            'photo' => ['required', 'image', 'max:2048'], // max está en KB (2048 = 2MB) [web:8]
        ]);

        $user = $request->user();

        // Borra la anterior si existe
        if (!empty($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
        }

        // Guarda en storage/app/public/avatars/{id}/...
        // store() genera un nombre único y retorna la ruta relativa dentro del disk [web:27]
        $path = $request->file('photo')->store('avatars/'.$user->id, 'public');

        $user->profile_photo_path = $path;
        $user->save();

        // Flash message de éxito para mostrar en la vista
        return back()->with('success', 'Foto actualizada');
    }

    public function destroy(Request $request)
    {
        $user = $request->user();

        if (!empty($user->profile_photo_path)) {
            Storage::disk('public')->delete($user->profile_photo_path);
            $user->profile_photo_path = null;
            $user->save();
        }

        return back()->with('success', 'Foto eliminada');
    }
}
