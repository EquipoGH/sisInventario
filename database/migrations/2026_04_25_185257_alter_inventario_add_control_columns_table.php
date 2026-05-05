<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->string('codigoinventario', 20)
                ->nullable()
                ->unique()
                ->after('id_inventario');

            $table->string('estadoinventario', 20)
                ->default('pendiente')
                ->after('fechafin');

            $table->unsignedBigInteger('usuarioregistro')
                ->nullable()
                ->after('responsable');

            $table->unsignedBigInteger('usuariocierre')
                ->nullable()
                ->after('usuarioregistro');

            $table->timestamp('fechacierre')
                ->nullable()
                ->after('observacion');

            $table->string('tipoinventario', 30)
                ->nullable()
                ->after('fechacierre');

            $table->foreign('usuarioregistro')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->foreign('usuariocierre')
                ->references('id')
                ->on('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();
        });

        $inventarios = DB::table('inventario')
            ->whereNull('codigoinventario')
            ->orderBy('id_inventario')
            ->get();

        foreach ($inventarios as $row) {
            DB::table('inventario')
                ->where('id_inventario', $row->id_inventario)
                ->update([
                    'codigoinventario' => 'INV-' . str_pad($row->id_inventario, 6, '0', STR_PAD_LEFT),
                ]);
        }
    }

    public function down(): void
    {
        Schema::table('inventario', function (Blueprint $table) {
            $table->dropForeign(['usuarioregistro']);
            $table->dropForeign(['usuariocierre']);

            $table->dropColumn([
                'codigoinventario',
                'estadoinventario',
                'usuarioregistro',
                'usuariocierre',
                'fechacierre',
                'tipoinventario',
            ]);
        });
    }
};