<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DetalleInventario extends Model
{
    use HasFactory;

    protected $table      = 'detalle_inventario';
    protected $primaryKey = 'id_detalle_inv';
    public $incrementing  = true;
    public $timestamps    = true;

    // Estados de verificación
    const VERIFICADO     = 'verificado';
    const NO_ENCONTRADO  = 'no_encontrado';
    const PENDIENTE      = 'pendiente';
    const OBSERVADO      = 'observado';

    protected $fillable = [
        'id_inventario',
        'id_movimiento',
        'estado_conservacion',
        'observacion',
        'estadoverificacion',
        'ubicaciondetectada',
        'usuarioverificador',
        'fechaverificacion',
        'requiereregularizacion',
        'evidencia',
    ];

    protected $casts = [
        'id_inventario'          => 'integer',
        'id_movimiento'          => 'integer',
        'estado_conservacion'    => 'integer',
        'ubicaciondetectada'     => 'integer',
        'usuarioverificador'     => 'integer',
        'fechaverificacion'      => 'datetime',
        'requiereregularizacion' => 'boolean',
        'created_at'             => 'datetime',
        'updated_at'             => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_detalle_inv';
    }

    // ==================== RELACIONES ====================

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id_inventario');
    }

    public function movimiento()
    {
        return $this->belongsTo(Movimiento::class, 'id_movimiento', 'id_movimiento');
    }

    public function estadoConservacion()
    {
        return $this->belongsTo(EstadoConservacion::class, 'estado_conservacion', 'id_estado_conservacion');
    }

    public function ubicacionDetectada()
    {
        return $this->belongsTo(Ubicacion::class, 'ubicaciondetectada', 'id_ubicacion');
    }

    public function usuarioVerificador()
    {
        return $this->belongsTo(User::class, 'usuarioverificador', 'id');
    }

    // ==================== MÉTODOS HELPER ====================

    public function getBadgeVerificacion(): string
    {
        return match (strtolower($this->estadoverificacion ?? 'pendiente')) {
            'verificado'    => '<span class="badge badge-success"><i class="fas fa-check"></i> Verificado</span>',
            'no_encontrado' => '<span class="badge badge-danger"><i class="fas fa-times"></i> No Encontrado</span>',
            'observado'     => '<span class="badge badge-warning"><i class="fas fa-exclamation-triangle"></i> Observado</span>',
            'pendiente'     => '<span class="badge badge-secondary"><i class="fas fa-clock"></i> Pendiente</span>',
            default         => '<span class="badge badge-light">' . htmlspecialchars($this->estadoverificacion ?? '-') . '</span>',
        };
    }

    public function getBadgeClaseVerificacion(): string
    {
        return match (strtolower($this->estadoverificacion ?? 'pendiente')) {
            'verificado'    => 'success',
            'no_encontrado' => 'danger',
            'observado'     => 'warning',
            'pendiente'     => 'secondary',
            default         => 'light',
        };
    }

    public function estaVerificado(): bool
    {
        return strtolower($this->estadoverificacion ?? '') === 'verificado';
    }

    // ==================== SCOPES ====================

    public function scopePorInventario($query, int $idInventario)
    {
        return $query->where('id_inventario', $idInventario);
    }

    public function scopeVerificados($query)
    {
        return $query->where('estadoverificacion', self::VERIFICADO);
    }

    public function scopePendientes($query)
    {
        return $query->where(function ($q) {
            $q->where('estadoverificacion', self::PENDIENTE)
              ->orWhereNull('estadoverificacion');
        });
    }

    public function scopeConRegularizacion($query)
    {
        return $query->where('requiereregularizacion', true);
    }
}
