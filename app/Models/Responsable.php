<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Responsable extends Model
{
    use HasFactory;

    protected $table = 'responsable';
    protected $primaryKey = 'dni_responsable';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'dni_responsable',
        'nombre_responsable',
        'apellidos_responsable',
        'cargo_responsable'
    ];

    public function getRouteKeyName()
    {
        return 'dni_responsable';
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor para nombre completo
     */
    protected function nombreCompleto(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper(trim($this->nombre_responsable . ' ' . $this->apellidos_responsable)),
        );
    }

    /**
     * Accessor para mostrar nombre en mayúsculas
     */
    protected function nombreResponsable(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    /**
     * Accessor para apellidos en mayúsculas
     */
    protected function apellidosResponsable(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    /**
     * Accessor para cargo en mayúsculas
     */
    protected function cargoResponsable(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Relación con ResponsableArea (asignaciones)
     * Un responsable puede tener múltiples asignaciones a áreas
     */
    public function responsableAreas()
    {
        return $this->hasMany(ResponsableArea::class, 'dni_responsable', 'dni_responsable');
    }

    /**
     * Relación Many-to-Many con Area a través de ResponsableArea
     * Un responsable puede estar asignado a múltiples áreas
     */
    public function areas()
    {
        return $this->belongsToMany(
            Area::class,
            'responsable_area',      // Tabla intermedia
            'dni_responsable',       // FK en tabla intermedia que apunta a este modelo
            'idarea',                // FK en tabla intermedia que apunta al modelo relacionado
            'dni_responsable',       // PK de este modelo
            'id_area'                // PK del modelo relacionado
        )->withPivot('fecha_asignacion');
    }

    /**
     * Relación con Movimiento
     * Un responsable puede tener múltiples movimientos
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'dni_responsable', 'dni_responsable');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Verificar si el responsable está asignado a un área específica
     */
    public function estaAsignadoAArea($areaId)
    {
        return $this->areas()->where('id_area', $areaId)->exists();
    }

    /**
     * Obtener todas las áreas del responsable
     */
    public function obtenerAreas()
    {
        return $this->areas()->get();
    }

    /**
     * Contar áreas asignadas
     */
    public function cantidadAreasAsignadas()
    {
        return $this->areas()->count();
    }

    /**
     * Scope para buscar responsables
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('dni_responsable', 'LIKE', "%{$termino}%")
              ->orWhere('nombre_responsable', 'ILIKE', "%{$termino}%")
              ->orWhere('apellidos_responsable', 'ILIKE', "%{$termino}%")
              ->orWhere('cargo_responsable', 'ILIKE', "%{$termino}%");
        });
    }
}
