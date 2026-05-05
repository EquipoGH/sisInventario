<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega la columna password_movil a la tabla users.
     * Esta contraseña es exclusiva para autenticarse desde la app móvil
     * y es completamente independiente del password del sistema web.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('password_movil')->nullable()->after('password')
                  ->comment('Contraseña exclusiva para login en la app móvil. Independiente del password web.');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('password_movil');
        });
    }
};
