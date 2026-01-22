<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponsableArea extends Model
{
    use HasFactory;

    protected $table = 'responsable_area';
    protected $primaryKey = 'id_responsable_area';
    public $incrementing = true;
    public $timestamps = false; // No tiene created_at/updated_at, solo fecha_asignacion

    protected $fillable = [
        'dni_responsable',
        'idarea',
        'fecha_asignacion'
    ];

    protected $dates = ['fecha_asignacion'];

    // Para route model binding
    public function getRouteKeyName()
    {
        return 'id_responsable_area';
    }

    // ==================== RELACIONES ====================

    /**
     * Relación con Responsable
     */
    public function responsable()
    {
        return $this->belongsTo(Responsable::class, 'dni_responsable', 'dni_responsable');
    }

    /**
     * Relación con Area
     */
    public function area()
    {
        return $this->belongsTo(Area::class, 'idarea', 'id_area');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Obtener nombre completo del responsable
     */
    public function getNombreCompletoAttribute()
    {
        if ($this->responsable) {
            return strtoupper(
                trim($this->responsable->nombre_responsable . ' ' . $this->responsable->apellidos_responsable)
            );
        }
        return 'N/A';
    }

    /**
     * Obtener nombre del área
     */
    public function getNombreAreaAttribute()
    {
        return $this->area ? strtoupper($this->area->nombre_area) : 'N/A';
    }

    /**
     * Verificar si ya existe la asignación
     */
    public static function existeAsignacion($dni, $areaId)
    {
        return self::where('dni_responsable', $dni)
                   ->where('idarea', $areaId)
                   ->exists();
    }

    /**
     * Scope para filtrar por responsable
     */
    public function scopePorResponsable($query, $dni)
    {
        return $query->where('dni_responsable', $dni);
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
            $q->where('dni_responsable', 'LIKE', "%{$termino}%")
              ->orWhereHas('responsable', function($q) use ($termino) {
                  $q->where('nombre_responsable', 'ILIKE', "%{$termino}%")
                    ->orWhere('apellidos_responsable', 'ILIKE', "%{$termino}%")
                    ->orWhere('cargo_responsable', 'ILIKE', "%{$termino}%");
              })
              ->orWhereHas('area', function($q) use ($termino) {
                  $q->where('nombre_area', 'ILIKE', "%{$termino}%");
              });
        });
    }

    /**
     * Obtener áreas de un responsable
     */
    public static function areasDeResponsable($dni)
    {
        return self::with('area')
                   ->where('dni_responsable', $dni)
                   ->get()
                   ->pluck('area');
    }

    /**
     * Obtener responsables de un área
     */
    public static function responsablesDeArea($areaId)
    {
        return self::with('responsable')
                   ->where('idarea', $areaId)
                   ->get()
                   ->pluck('responsable');
    }
}
