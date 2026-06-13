<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Incidencia extends Model
{
    use HasFactory;

    protected $table      = 'incidencias';
    protected $primaryKey = 'id_incidencia';
    public $incrementing  = true;
    public $timestamps    = true;

    // Tipos de incidencia
    const TIPO_SOBRANTE    = 'sobrante';
    const TIPO_FALTANTE    = 'faltante';
    const TIPO_SIN_CODIGO  = 'sin_codigo';
    const TIPO_DETERIORADO = 'deteriorado';

    // Estados
    const ESTADO_REVISADO    = 'revisado';
    const ESTADO_NO_REVISADO = 'no_revisado';

    protected $fillable = [
        'id_inventario',
        'id_bien',
        'tipo_incidencia',
        'id_ubicacion',
        'id_area',
        'fecha_registro',
        'observacion',
        'resolucion',
        'id_usuario_revision',
        'fecha_revision',
        'img_bien',
        'estado',
    ];

    protected $casts = [
        'id_inventario'       => 'integer',
        'id_bien'             => 'integer',
        'id_ubicacion'        => 'integer',
        'id_area'             => 'integer',
        'fecha_registro'      => 'datetime',
        'fecha_revision'      => 'datetime',
        'id_usuario_revision' => 'integer',
    ];

    // ==================== RELACIONES ====================

    public function inventario()
    {
        return $this->belongsTo(Inventario::class, 'id_inventario', 'id_inventario');
    }

    public function usuarioRevision()
    {
        return $this->belongsTo(User::class, 'id_usuario_revision', 'id');
    }


    public function bien()
    {
        return $this->belongsTo(Bien::class, 'id_bien', 'id_bien');
    }

    public function ubicacion()
    {
        return $this->belongsTo(Ubicacion::class, 'id_ubicacion', 'id_ubicacion');
    }

    public function area()
    {
        return $this->belongsTo(Area::class, 'id_area', 'id_area');
    }

    // ==================== MÉTODOS HELPER ====================

    public function getBadgeEstado(): string
    {
        return match ($this->estado) {
            self::ESTADO_REVISADO    => '<span class="badge badge-success"><i class="fas fa-check-double"></i> Revisado</span>',
            self::ESTADO_NO_REVISADO => '<span class="badge badge-warning"><i class="fas fa-clock"></i> No Revisado</span>',
            default                 => '<span class="badge badge-secondary">' . htmlspecialchars($this->estado) . '</span>',
        };
    }

    public function getBadgeTipo(): string
    {
        return match ($this->tipo_incidencia) {
            self::TIPO_SOBRANTE    => '<span class="badge badge-info">Sobrante</span>',
            self::TIPO_FALTANTE    => '<span class="badge badge-danger">Faltante</span>',
            self::TIPO_SIN_CODIGO  => '<span class="badge badge-dark">Sin Código</span>',
            self::TIPO_DETERIORADO => '<span class="badge badge-warning">Deteriorado</span>',
            default                => '<span class="badge badge-light">' . htmlspecialchars($this->tipo_incidencia) . '</span>',
        };
    }
}
