<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Mover "Movimientos" como sub-sección de "Gestión De Bienes":
     *  1) Actualiza route_prefix del módulo Bienes para incluir movimiento.*
     *  2) Inactiva el módulo Movimientos (ya no aparece en el sidebar como ítem independiente)
     */
    public function up(): void
    {
        // 1) Actualizar route_prefix del módulo Gestión De Bienes (ID = 1)
        DB::table('modulos')
            ->where('idmodulo', 1)
            ->update(['route_prefix' => 'bien.*,movimiento.*']);

        // 2) Inactivar el módulo Movimientos (ID = 2)
        DB::table('modulos')
            ->where('idmodulo', 2)
            ->update(['estadomodulo' => 'I']);
    }

    public function down(): void
    {
        // Revertir: restaurar route_prefix original de Bienes
        DB::table('modulos')
            ->where('idmodulo', 1)
            ->update(['route_prefix' => 'bien.*']);

        // Reactivar el módulo Movimientos
        DB::table('modulos')
            ->where('idmodulo', 2)
            ->update(['estadomodulo' => 'A']);
    }
};
