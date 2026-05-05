<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_bien', function (Blueprint $table) {
            $table->increments('id_tipo_bien'); // Esto crea auto-increment y primary key automáticamente
            $table->string('nombre_tipo', 20);
            $table->timestamps();
        }); 
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_bien');
    }
};
