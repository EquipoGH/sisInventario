<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('system_settings', function (Blueprint $table) {
            $table->id();

            $table->string('key', 100)->unique();     // ej: nombre_sistema, logo_path
            $table->longText('value')->nullable();    // ej: "GesInventario" o "branding/logo.png"
            $table->string('type', 30)->default('string'); // string, text, bool, color, image, json

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_settings');
    }
};
