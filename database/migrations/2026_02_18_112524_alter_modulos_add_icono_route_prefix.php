<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->string('icono', 80)->nullable()->after('etiqueta');         // ej: fas fa-cog
            $table->string('route_prefix', 80)->nullable()->after('icono');    // ej: configuracion.*
        });
    }

    public function down(): void
    {
        Schema::table('modulos', function (Blueprint $table) {
            $table->dropColumn(['icono', 'route_prefix']);
        });
    }
};
