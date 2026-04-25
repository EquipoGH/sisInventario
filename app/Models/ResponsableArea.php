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
        'fecha_asignacion',
        'periodo_anio'
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
    public static function existeAsignacion($dni, $areaId, $anio = null)
    {
        $anio = $anio ?? date('Y');
        return self::where('dni_responsable', $dni)
                   ->where('idarea', $areaId)
                   ->where('periodo_anio', $anio)
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
        $termLower = strtolower($termino);
        return $query->where(function($q) use ($termino, $termLower) {
            $q->where('dni_responsable', 'LIKE', "%{$termino}%")
              ->orWhereHas('responsable', function($q) use ($termLower) {
                  $q->whereRaw('LOWER(nombre_responsable) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(apellidos_responsable) LIKE ?', ["%{$termLower}%"])
                    ->orWhereRaw('LOWER(cargo_responsable) LIKE ?', ["%{$termLower}%"]);
              })
              ->orWhereHas('area', function($q) use ($termLower) {
                  $q->whereRaw('LOWER(nombre_area) LIKE ?', ["%{$termLower}%"]);
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
