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
        Schema::table('bien', function (Blueprint $table) {
            $table->unsignedBigInteger('registrado_por')->nullable()->after('activo');

            $table->foreign('registrado_por')
                  ->references('id')
                  ->on('users')
                  ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bien', function (Blueprint $table) {
            $table->dropForeign(['registrado_por']);
            $table->dropColumn('registrado_por');
        });
    }
};
