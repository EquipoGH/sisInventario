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

    // ⭐ IMPORTANTE: Desactivar timestamps automáticos de Laravel
    public $timestamps = false;

    protected $fillable = [
        'idbien',
        'tipo_mvto',
        'fecha_mvto',
        'detalle_tecnico',
        'documento_sustentatorio',
        'NumDocto',
        'idubicacion',
        'id_estado_conservacion_bien',
        'idusuario',
        // ⭐⭐⭐ NUEVOS CAMPOS PARA REVERSIÓN ⭐⭐⭐
        'revertido',
        'revertido_por',
        'fecha_reversion',
        'movimiento_reversion_id'
    ];

    // ⭐⭐⭐ CAMBIO CRÍTICO: Usar $casts en vez de $dates ⭐⭐⭐
    protected $casts = [
        'fecha_mvto' => 'datetime',
        'fecha_reversion' => 'datetime', // ⭐ NUEVO
        'idbien' => 'integer',
        'tipo_mvto' => 'integer',
        'idubicacion' => 'integer',
        'id_estado_conservacion_bien' => 'integer',
        'idusuario' => 'integer',
        'revertido' => 'boolean', // ⭐ NUEVO
        'revertido_por' => 'integer', // ⭐ NUEVO
        'movimiento_reversion_id' => 'integer' // ⭐ NUEVO
    ];

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
        return $this->belongsTo(EstadoBien::class, 'id_estado_conservacion_bien', 'id_estado_bien');
    }


    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario', 'id');
    }

    // ⭐⭐⭐ NUEVAS RELACIONES PARA REVERSIÓN ⭐⭐⭐

    /**
     * Usuario que revirtió este movimiento
     */
    public function usuarioReversion()
    {
        return $this->belongsTo(User::class, 'revertido_por', 'id');
    }

    /**
     * Movimiento de reversión asociado (si este fue revertido)
     */
    public function movimientoReversion()
    {
        return $this->belongsTo(Movimiento::class, 'movimiento_reversion_id', 'id_movimiento');
    }

    /**
     * Movimiento original que fue revertido (si este es una reversión)
     */
    public function movimientoOriginalRevertido()
    {
        return $this->hasOne(Movimiento::class, 'movimiento_reversion_id', 'id_movimiento');
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

    // ⭐⭐⭐ NUEVOS SCOPES PARA REVERSIÓN ⭐⭐⭐

    /**
     * Solo movimientos revertidos
     */
    public function scopeRevertidos($query)
    {
        return $query->where('revertido', true);
    }

    /**
     * Solo movimientos NO revertidos
     */
    public function scopeNoRevertidos($query)
    {
        return $query->where('revertido', false);
    }

    /**
     * Solo movimientos de tipo BAJA
     */
    public function scopeBajas($query)
    {
        return $query->whereHas('tipoMovimiento', function($q) {
            $q->where('tipo_mvto', 'ILIKE', '%baja%');
        });
    }

    /**
     * Bajas que pueden ser revertidas (no revertidas aún)
     */
    public function scopeBajasRevertibles($query)
    {
        return $query->bajas()->noRevertidos();
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Verificar si este movimiento es de tipo BAJA
     */
    public function esBaja(): bool
    {
        if (!$this->tipoMovimiento) {
            return false;
        }
        return stripos($this->tipoMovimiento->tipo_mvto, 'baja') !== false;
    }

    /**
     * Verificar si este movimiento puede ser revertido
     */
    public function puedeSerRevertido(): bool
    {
        return $this->esBaja() && !$this->revertido;
    }

    /**
     * Verificar si este movimiento es una reversión
     */
    public function esReversion(): bool
    {
        if (!$this->tipoMovimiento) {
            return false;
        }
        return stripos($this->tipoMovimiento->tipo_mvto, 'revers') !== false;
    }

    /**
     * Obtener información de la reversión (si existe)
     */
    public function getInfoReversion(): ?array
    {
        if (!$this->revertido) {
            return null;
        }

        return [
            'revertido' => true,
            'fecha_reversion' => $this->fecha_reversion,
            'revertido_por' => $this->usuarioReversion ? [
                'id' => $this->usuarioReversion->id,
                'nombre' => $this->usuarioReversion->name,
                'email' => $this->usuarioReversion->email
            ] : null,
            'movimiento_reversion_id' => $this->movimiento_reversion_id
        ];
    }
}
