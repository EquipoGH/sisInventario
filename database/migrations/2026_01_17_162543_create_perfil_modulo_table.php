<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('perfil_modulo', function (Blueprint $table) {
            $table->id('idperfilmodulo');
            $table->foreignId('idperfil')->constrained('perfil', 'idperfil')->onDelete('cascade');
            $table->foreignId('idmodulo')->constrained('modulos', 'idmodulo')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('perfil_modulo');
    }
};
