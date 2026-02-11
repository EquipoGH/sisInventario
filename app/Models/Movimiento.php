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
        // ⭐⭐⭐ CAMPOS PARA REVERSIÓN ⭐⭐⭐
        'revertido',
        'revertido_por',
        'fecha_reversion',
        'movimiento_reversion_id',
        // ⭐⭐⭐ NUEVOS CAMPOS PARA SOFT DELETE (ANULACIÓN) ⭐⭐⭐
        'anulado',
        'anulado_por',
        'fecha_anulacion',
        'motivo_anulacion'
    ];

    // ⭐⭐⭐ CAMBIO CRÍTICO: Usar $casts en vez de $dates ⭐⭐⭐
    protected $casts = [
        'fecha_mvto' => 'datetime',
        'fecha_reversion' => 'datetime',
        'fecha_anulacion' => 'datetime',  // ⭐ NUEVO
        'idbien' => 'integer',
        'tipo_mvto' => 'integer',
        'idubicacion' => 'integer',
        'id_estado_conservacion_bien' => 'integer',
        'idusuario' => 'integer',
        'revertido' => 'boolean',
        'revertido_por' => 'integer',
        'movimiento_reversion_id' => 'integer',
        'anulado' => 'boolean',  // ⭐ NUEVO
        'anulado_por' => 'integer'  // ⭐ NUEVO
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

    /**
     * ✅ CORREGIDO: Foreign key correcta
     */
    public function estadoConservacion()
    {
        return $this->belongsTo(EstadoBien::class, 'id_estado_conservacion_bien', 'id_estado');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario', 'id');
    }

    // ⭐⭐⭐ RELACIONES PARA REVERSIÓN ⭐⭐⭐

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

    // ⭐⭐⭐ NUEVAS RELACIONES PARA SOFT DELETE ⭐⭐⭐

    /**
     * Usuario que anuló este movimiento
     */
    public function usuarioAnulo()
    {
        return $this->belongsTo(User::class, 'anulado_por', 'id');
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

    // ⭐⭐⭐ SCOPES PARA REVERSIÓN ⭐⭐⭐

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

    // ⭐⭐⭐ NUEVOS SCOPES PARA SOFT DELETE ⭐⭐⭐

    /**
     * Solo movimientos ACTIVOS (no anulados)
     */
    public function scopeActivos($query)
    {
        return $query->where('anulado', false);
    }

    /**
     * Solo movimientos ANULADOS
     */
    public function scopeAnulados($query)
    {
        return $query->where('anulado', true);
    }

    /**
     * Movimientos que NO están anulados NI revertidos
     */
    public function scopeVigentes($query)
    {
        return $query->where('anulado', false)
                     ->where('revertido', false);
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
        return $this->esBaja() && !$this->revertido && !$this->anulado;
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
     * Verificar si este movimiento está anulado
     */
    public function estaAnulado(): bool
    {
        return $this->anulado === true;
    }

    /**
     * Verificar si este movimiento puede ser anulado
     */
    public function puedeSerAnulado(): bool
    {
        // No se puede anular si ya está anulado o si ya fue revertido
        return !$this->anulado && !$this->revertido;
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

    /**
     * ⭐⭐⭐ NUEVO: Obtener información de la anulación (si existe) ⭐⭐⭐
     */
    public function getInfoAnulacion(): ?array
    {
        if (!$this->anulado) {
            return null;
        }

        return [
            'anulado' => true,
            'fecha_anulacion' => $this->fecha_anulacion,
            'motivo_anulacion' => $this->motivo_anulacion,
            'anulado_por' => $this->usuarioAnulo ? [
                'id' => $this->usuarioAnulo->id,
                'nombre' => $this->usuarioAnulo->name,
                'email' => $this->usuarioAnulo->email
            ] : null
        ];
    }

    /**
     * ⭐⭐⭐ NUEVO: Obtener estado completo del movimiento ⭐⭐⭐
     */
    public function getEstadoCompleto(): array
    {
        return [
            'activo' => !$this->anulado && !$this->revertido,
            'anulado' => $this->anulado,
            'revertido' => $this->revertido,
            'puede_revertir' => $this->puedeSerRevertido(),
            'puede_anular' => $this->puedeSerAnulado(),
            'es_baja' => $this->esBaja(),
            'es_reversion' => $this->esReversion()
        ];
    }

    /**
     * ⭐⭐⭐ NUEVO: Obtener badge de estado visual ⭐⭐⭐
     */
    public function getBadgeEstado(): string
    {
        if ($this->anulado) {
            return '<span class="badge badge-dark" title="Anulado el ' .
                   $this->fecha_anulacion->format('d/m/Y H:i') .
                   ' por ' . ($this->usuarioAnulo->name ?? 'Sistema') .
                   '"><i class="fas fa-ban"></i> ANULADO</span>';
        }

        if ($this->revertido) {
            return '<span class="badge badge-info" title="Revertido el ' .
                   $this->fecha_reversion->format('d/m/Y H:i') .
                   ' por ' . ($this->usuarioReversion->name ?? 'Sistema') .
                   '"><i class="fas fa-undo"></i> REVERTIDO</span>';
        }

        return '<span class="badge badge-success"><i class="fas fa-check"></i> VIGENTE</span>';
    }
}
