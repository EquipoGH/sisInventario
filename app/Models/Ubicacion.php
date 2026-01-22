<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Ubicacion extends Model
{
    use HasFactory;

    protected $table = 'ubicacion';
    protected $primaryKey = 'id_ubicacion';
    public $incrementing = true;

    protected $fillable = [
        'nombre_sede',
        'ambiente',
        'piso_ubicacion',
        'idarea'
    ];

    // Para route model binding
    public function getRouteKeyName()
    {
        return 'id_ubicacion';
    }

    // Accessor para nombre de sede en mayúsculas
    protected function nombreSede(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Accessor para ambiente en mayúsculas
    protected function ambiente(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Accessor para piso en mayúsculas
    protected function pisoUbicacion(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Accessor para obtener ubicación completa
    protected function ubicacionCompleta(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper("{$this->nombre_sede} - {$this->ambiente} - Piso {$this->piso_ubicacion}"),
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Relación con Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'idarea', 'id_area');
    }

    /**
     * Relación con Movimientos
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'idubicacion', 'id_ubicacion');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Verificar si tiene movimientos asociados
     */
    public function tieneMovimientos()
    {
        return $this->movimientos()->exists();
    }

    /**
     * Scope para filtrar por área
     */
    public function scopePorArea($query, $areaId)
    {
        return $query->where('idarea', $areaId);
    }

    /**
     * Scope para búsqueda general
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('nombre_sede', 'ILIKE', "%{$termino}%")
              ->orWhere('ambiente', 'ILIKE', "%{$termino}%")
              ->orWhere('piso_ubicacion', 'ILIKE', "%{$termino}%");
        });
    }

}

