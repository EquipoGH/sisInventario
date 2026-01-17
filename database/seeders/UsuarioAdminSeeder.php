<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UsuarioAdminSeeder extends Seeder
{
    public function run(): void
    {
        User::create([
            'dni_usuario' => '12345678',
            'name' => 'Administrador',
            'email' => 'admin@sistema.com',
            'password' => Hash::make('admin123'),
            'rol_usuario' => 'Administrador',
            'estado_usuario' => 'A',
            'email_verified_at' => now(),
        ]);
    }
}
