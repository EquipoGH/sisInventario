<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documento_sustento', function (Blueprint $table) {
            $table->id('id_documento');
            $table->string('tipo_documento', 20);
            $table->string('numero_documento', 20);
            $table->date('fecha_documento');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documento_sustento');
    }
};
