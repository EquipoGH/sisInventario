<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('responsable_area', function (Blueprint $table) {
            $table->dropUnique('unique_responsable_area');
            $table->integer('periodo_anio')->default(date('Y'))->after('idarea');
            $table->unique(['dni_responsable', 'idarea', 'periodo_anio'], 'unique_responsable_area_anio');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('responsable_area', function (Blueprint $table) {
            $table->dropUnique('unique_responsable_area_anio');
            $table->dropColumn('periodo_anio');
            $table->unique(['dni_responsable', 'idarea'], 'unique_responsable_area');
        });
    }
};
