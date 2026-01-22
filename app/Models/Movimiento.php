<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Movimiento extends Model
{
    use HasFactory;

    protected $table = 'movimiento';
    protected $primaryKey = 'id_movimiento';
    public $incrementing = true;

    protected $fillable = [
        'idbien',
        'tipo_mvto',
        'fecha_mvto',
        'detalle_tecnico',
        // 'detalle_administrativo',
        'documento_sustentatorio',
        'idubicacion',
        'id_estado_conservacion_bien',  // ✅ CORREGIDO
        'idusuario'
    ];

    protected $dates = ['fecha_mvto'];

    public function getRouteKeyName()
    {
        return 'id_movimiento';
    }

    // ==================== RELACIONES ====================

    /**
     * Relación con Bien
     */
    public function bien()
    {
        return $this->belongsTo(Bien::class, 'idbien', 'id_bien');
    }

    /**
     * Relación con TipoMvto
     */
    public function tipoMovimiento()
    {
        return $this->belongsTo(TipoMvto::class, 'tipo_mvto', 'id_tipo_mvto');
    }

    /**
     * Relación con DocumentoSustento
     */
    public function documentoSustento()
    {
        return $this->belongsTo(DocumentoSustento::class, 'documento_sustentatorio', 'id_documento');
    }

    /**
     * Relación con Ubicacion
     */
    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'idubicacion', 'id_ubicacion');
    }

    /**
     * Relación con EstadoBien
     * ✅ CORREGIDO: Usar el nombre real de la columna
     */
    public function estadoConservacion()
    {
        return $this->belongsTo(EstadoBien::class, 'id_estado_conservacion_bien', 'id_estado');
    }

    /**
     * Relación con Usuario
     */
    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario', 'id');
    }

    // ==================== SCOPES ====================

    /**
     * Scope para filtrar por bien
     */
    public function scopePorBien($query, $bienId)
    {
        return $query->where('idbien', $bienId);
    }

    /**
     * Scope para filtrar por tipo de movimiento
     */
    public function scopePorTipo($query, $tipoId)
    {
        return $query->where('tipo_mvto', $tipoId);
    }

    /**
     * Scope para filtrar por fecha
     */
    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_mvto', [$desde, $hasta]);
    }

    /**
     * Scope para búsqueda general
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('id_movimiento', 'LIKE', "%{$termino}%")
              ->orWhere('detalle_tecnico', 'ILIKE', "%{$termino}%")
              ->orWhere('detalle_administrativo', 'ILIKE', "%{$termino}%")
              ->orWhereHas('bien', function($q) use ($termino) {
                  $q->where('codigo_patrimonial', 'ILIKE', "%{$termino}%")
                    ->orWhere('denominacion_bien', 'ILIKE', "%{$termino}%");
              });
        });
    }
}
