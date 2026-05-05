<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\TipoMvto;

class TipoMvtoSeeder extends Seeder
{
    public function run(): void
    {
        $tipos = [
            'Alta',
            'Asignación',
            'Traslado',
            'Baja',
        ];

        foreach ($tipos as $tipo) {
            TipoMvto::updateOrCreate(
                ['tipo_mvto' => $tipo],
                ['tipo_mvto' => $tipo]
            );
        }
    }
}