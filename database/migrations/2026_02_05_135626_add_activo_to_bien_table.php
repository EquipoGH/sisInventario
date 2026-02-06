<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('bien', function (Blueprint $table) {
            $table->boolean('activo')->default(true)->after('foto_bien');
            $table->timestamp('eliminado_en')->nullable()->after('activo'); // opcional
        });
    }

    public function down(): void
    {
        Schema::table('bien', function (Blueprint $table) {
            $table->dropColumn(['activo', 'eliminado_en']);
        });
    }
};
