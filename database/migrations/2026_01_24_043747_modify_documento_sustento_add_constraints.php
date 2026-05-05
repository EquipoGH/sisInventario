<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // 🔴 CRÍTICO: Verificar duplicados (CORREGIDO PARA PostgreSQL)
        $duplicados = DB::select("
            SELECT numero_documento, COUNT(*) as total
            FROM documento_sustento
            GROUP BY numero_documento
            HAVING COUNT(*) > 1
        ");

        if (count($duplicados) > 0) {
            echo "\n⚠️  DUPLICADOS ENCONTRADOS:\n";
            foreach ($duplicados as $dup) {
                echo "   - {$dup->numero_documento} ({$dup->total} veces)\n";
            }
            throw new \Exception('Existen números de documento duplicados. Elimina los duplicados antes de ejecutar esta migración.');
        }

        Schema::table('documento_sustento', function (Blueprint $table) {
            // 1️⃣ AGREGAR UNIQUE constraint a numero_documento
            $table->unique('numero_documento');

            // 2️⃣ AMPLIAR tipo_documento de 20 a 50 caracteres
            $table->string('tipo_documento', 50)->change();

            // 3️⃣ AGREGAR ÍNDICES para búsquedas rápidas
            $table->index('tipo_documento');
            $table->index('fecha_documento');
        });
    }

    public function down(): void
    {
        Schema::table('documento_sustento', function (Blueprint $table) {
            // Eliminar índices
            $table->dropIndex(['tipo_documento']);
            $table->dropIndex(['fecha_documento']);

            // Eliminar unique constraint
            $table->dropUnique(['numero_documento']);

            // Revertir cambio de longitud
            $table->string('tipo_documento', 20)->change();
        });
    }
};
