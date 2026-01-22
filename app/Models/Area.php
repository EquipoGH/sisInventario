<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Area extends Model
{
    use HasFactory;

    protected $table = 'area';
    protected $primaryKey = 'id_area';
    public $incrementing = true;

    protected $fillable = [
        'nombre_area'
    ];

    public function getRouteKeyName()
    {
        return 'id_area';
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor para mostrar nombre en mayúsculas
     */
    protected function nombreArea(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Relación con Ubicacion
     * Un área puede tener múltiples ubicaciones
     */
    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'idarea', 'id_area');
    }

    /**
     * Relación con ResponsableArea (asignaciones)
     * Un área puede tener múltiples asignaciones de responsables
     */
    public function responsableAreas()
    {
        return $this->hasMany(ResponsableArea::class, 'idarea', 'id_area');
    }

    /**
     * Relación Many-to-Many con Responsable a través de ResponsableArea
     * Un área puede tener múltiples responsables asignados
     */
    public function responsables()
    {
        return $this->belongsToMany(
            Responsable::class,
            'responsable_area',      // Tabla intermedia
            'idarea',                // FK en tabla intermedia que apunta a este modelo
            'dni_responsable',       // FK en tabla intermedia que apunta al modelo relacionado
            'id_area',               // PK de este modelo
            'dni_responsable'        // PK del modelo relacionado
        )->withPivot('fecha_asignacion');
    }

    /**
     * Relación con Movimiento
     * Un área puede tener múltiples movimientos
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'idarea', 'id_area');
    }

    /**
     * Relación con Bien
     * Un área puede tener múltiples bienes
     */
    public function bienes()
    {
        return $this->hasMany(Bien::class, 'idarea', 'id_area');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Verificar si un responsable está asignado a esta área
     */
    public function tieneResponsable($dni)
    {
        return $this->responsables()->where('dni_responsable', $dni)->exists();
    }

    /**
     * Obtener todos los responsables del área
     */
    public function obtenerResponsables()
    {
        return $this->responsables()->get();
    }

    /**
     * Contar responsables asignados
     */
    public function cantidadResponsables()
    {
        return $this->responsables()->count();
    }

    /**
     * Contar ubicaciones del área
     */
    public function cantidadUbicaciones()
    {
        return $this->ubicaciones()->count();
    }

    /**
     * Verificar si el área tiene ubicaciones
     */
    public function tieneUbicaciones()
    {
        return $this->ubicaciones()->exists();
    }

    /**
     * Verificar si el área tiene responsables
     */
    public function tieneResponsables()
    {
        return $this->responsables()->exists();
    }

    /**
     * Scope para buscar áreas
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where('nombre_area', 'ILIKE', "%{$termino}%");
    }
}
