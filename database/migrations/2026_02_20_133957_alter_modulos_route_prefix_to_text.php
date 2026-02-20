<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
{
    Schema::table('modulos', function (Blueprint $table) {
        $table->text('route_prefix')->nullable()->change();
    });
}

public function down(): void
{
    Schema::table('modulos', function (Blueprint $table) {
        $table->string('route_prefix', 80)->nullable()->change();
    });
}

};
