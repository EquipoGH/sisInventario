<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('responsable', function (Blueprint $table) {
            $table->string('dni_responsable', 8)->primary();
            $table->string('nombre_responsable', 20);
            $table->string('apellidos_responsable', 20);
            $table->string('cargo_responsable', 20);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('responsable');
    }
};
