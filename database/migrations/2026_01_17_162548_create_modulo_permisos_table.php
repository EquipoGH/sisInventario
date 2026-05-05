<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('modulo_permisos', function (Blueprint $table) {
            $table->id('idmodulopermiso');
            $table->foreignId('idperfilmodulo')->constrained('perfil_modulo', 'idperfilmodulo')->onDelete('cascade');
            $table->foreignId('idpermiso')->constrained('permisos', 'idpermiso')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('modulo_permisos');
    }
};
