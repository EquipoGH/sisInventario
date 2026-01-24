<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->string('NumDocto', 20)->nullable()->after('documento_sustentatorio');
        });
    }

    public function down(): void
    {
        Schema::table('movimiento', function (Blueprint $table) {
            $table->dropColumn('NumDocto');
        });
    }
};
