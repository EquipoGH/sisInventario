<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Perfil;
use App\Models\UsuarioPerfil;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::updateOrCreate(
            ['email' => 'admin@sistema.com'],
            [
                'dni_usuario' => '12345678',
                'name' => 'Administrador',
                'password' => Hash::make('admin123'),
                'rol_usuario' => 'ADMIN',
                'estado_usuario' => 'A',
                'email_verified_at' => now(),
            ]
        );

        $perfil = Perfil::firstOrCreate([
            'nomperfil' => 'Admin',
        ]);

        UsuarioPerfil::firstOrCreate([
            'idusuario' => $user->id,
            'idperfil'  => $perfil->idperfil,
        ]);
    }
}
