<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('configuracion_sistema', function (Blueprint $table) {
            $table->id();
            $table->string('clave')->unique();
            $table->text('valor');
            $table->string('tipo')->default('text');
            $table->string('grupo')->default('general');
            $table->string('descripcion')->nullable();
            $table->timestamps();
        });

        // 🎨 INSERTAR CONFIGURACIONES POR DEFECTO
        DB::table('configuracion_sistema')->insert([
            // HEADERS DE MODALES
            [
                'clave' => 'color_header_crear',
                'valor' => '#007bff',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color header modal CREAR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'color_header_editar',
                'valor' => '#17a2b8',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color header modal EDITAR',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'color_header_eliminar',
                'valor' => '#dc3545',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color header modal ELIMINAR',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // TABLAS
            [
                'clave' => 'color_tabla_header',
                'valor' => '#343a40',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color header de tablas',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'color_tabla_hover',
                'valor' => '#e3f2fd',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color hover en filas de tabla',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // BOTONES
            [
                'clave' => 'color_btn_primario',
                'valor' => '#007bff',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color botón primario',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'color_btn_success',
                'valor' => '#28a745',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color botón success',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'color_btn_danger',
                'valor' => '#dc3545',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color botón eliminar',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // BADGES
            [
                'clave' => 'color_badge_info',
                'valor' => '#17a2b8',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color badge info',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'color_badge_warning',
                'valor' => '#ffc107',
                'tipo' => 'color',
                'grupo' => 'colores',
                'descripcion' => 'Color badge warning',
                'created_at' => now(),
                'updated_at' => now()
            ],

            // CONFIGURACIONES GENERALES
            [
                'clave' => 'nombre_sistema',
                'valor' => 'GesInventario',
                'tipo' => 'text',
                'grupo' => 'general',
                'descripcion' => 'Nombre del sistema',
                'created_at' => now(),
                'updated_at' => now()
            ],
            [
                'clave' => 'items_por_pagina',
                'valor' => '10',
                'tipo' => 'number',
                'grupo' => 'general',
                'descripcion' => 'Registros por página',
                'created_at' => now(),
                'updated_at' => now()
            ],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('configuracion_sistema');
    }
};
