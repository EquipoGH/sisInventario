<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ubicacion', function (Blueprint $table) {
            $table->id('id_ubicacion');
            $table->string('nombre_sede', 100);
            $table->string('ambiente', 100);
            $table->string('piso_ubicacion', 100);
            $table->unsignedBigInteger('idarea');
            $table->timestamps();

            // Foreign Key
            $table->foreign('idarea')
                  ->references('id_area')
                  ->on('area')
                  ->onDelete('restrict')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ubicacion');
    }
};
