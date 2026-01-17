<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('usuario_perfil', function (Blueprint $table) {
            $table->id('idusuarioperfil');
            $table->foreignId('idusuario')->constrained('users', 'id')->onDelete('cascade');
            $table->foreignId('idperfil')->constrained('perfil', 'idperfil')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_perfil');
    }
};
