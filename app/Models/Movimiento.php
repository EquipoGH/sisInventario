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
        'documento_sustentatorio',
        'NumDocto',  // ⭐ NUEVO
        'idubicacion',
        'id_estado_conservacion_bien',
        'idusuario'
    ];

    protected $dates = ['fecha_mvto'];

    public function getRouteKeyName()
    {
        return 'id_movimiento';
    }

    // ==================== RELACIONES ====================

    public function bien()
    {
        return $this->belongsTo(Bien::class, 'idbien', 'id_bien');
    }

    public function tipoMovimiento()
    {
        return $this->belongsTo(TipoMvto::class, 'tipo_mvto', 'id_tipo_mvto');
    }

    public function documentoSustento()
    {
        return $this->belongsTo(DocumentoSustento::class, 'documento_sustentatorio', 'id_documento');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'idubicacion', 'id_ubicacion');
    }

    public function estadoConservacion()
    {
        return $this->belongsTo(EstadoBien::class, 'id_estado_conservacion_bien', 'id_estado');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario', 'id');
    }

    // ==================== SCOPES ====================

    public function scopePorBien($query, $bienId)
    {
        return $query->where('idbien', $bienId);
    }

    public function scopePorTipo($query, $tipoId)
    {
        return $query->where('tipo_mvto', $tipoId);
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_mvto', [$desde, $hasta]);
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('id_movimiento', 'LIKE', "%{$termino}%")
              ->orWhere('detalle_tecnico', 'ILIKE', "%{$termino}%")
              ->orWhere('NumDocto', 'ILIKE', "%{$termino}%")
              ->orWhereHas('bien', function($q) use ($termino) {
                  $q->where('codigo_patrimonial', 'ILIKE', "%{$termino}%")
                    ->orWhere('denominacion_bien', 'ILIKE', "%{$termino}%");
              });
        });
    }
}
