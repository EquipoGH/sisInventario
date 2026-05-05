<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Perfil;

class PerfilesBaseSeeder extends Seeder
{
    public function run(): void
    {
        $perfiles = ['Admin', 'Informática', 'Invitado'];

        foreach ($perfiles as $nomperfil) {
            Perfil::updateOrCreate(
                ['nomperfil' => $nomperfil],
                ['nomperfil' => $nomperfil]
            );
        }
    }
}