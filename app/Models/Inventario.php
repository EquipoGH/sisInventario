<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Inventario extends Model
{
    use HasFactory;

    protected $table      = 'inventario';
    protected $primaryKey = 'id_inventario';
    public $incrementing  = true;
    public $timestamps    = true;

    // Estados del inventario
    const ESTADO_PENDIENTE  = 'pendiente';
    const ESTADO_EN_PROCESO = 'en_proceso';
    const ESTADO_CERRADO    = 'cerrado';
    const ESTADO_ANULADO    = 'anulado';

    // Tipos de inventario (conforme a normativa SBN - Perú)
    const TIPO_FISICO_ANUAL        = 'Inventario Físico Anual';
    const TIPO_CAMBIO_PERSONAL     = 'Inventario por Cambio de Personal';
    const TIPO_TRANSFERENCIA       = 'Inventario por Transferencia';
    const TIPO_VERIFICACION        = 'Inventario de Verificación';
    const TIPO_BAJA                = 'Inventario de Baja';
    const TIPO_SORPRESA            = 'Inventario Sorpresa';

    protected $fillable = [
        'fecha_inicio',
        'fecha_fin',
        'responsable',
        'observacion',
        'codigoinventario',
        'estadoinventario',
        'usuarioregistro',
        'usuariocierre',
        'fechacierre',
        'tipoinventario',
    ];

    protected $casts = [
        'fecha_inicio'  => 'date',
        'fecha_fin'     => 'date',
        'fechacierre'   => 'datetime',
        'created_at'    => 'datetime',
        'updated_at'    => 'datetime',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_inventario';
    }

    // ==================== RELACIONES ====================

    public function responsablePersona()
    {
        return $this->belongsTo(Responsable::class, 'responsable', 'dni_responsable');
    }

    public function usuarioRegistro()
    {
        return $this->belongsTo(User::class, 'usuarioregistro', 'id');
    }

    public function usuarioCierre()
    {
        return $this->belongsTo(User::class, 'usuariocierre', 'id');
    }

    public function detalles()
    {
        return $this->hasMany(DetalleInventario::class, 'id_inventario', 'id_inventario');
    }

    // ==================== ACCESSORS ====================

    protected function estadoinventario(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst(strtolower($value ?? 'pendiente')),
        );
    }

    // ==================== MÉTODOS HELPER ====================

    public function getBadgeEstado(): string
    {
        return match (strtolower($this->getRawOriginal('estadoinventario') ?? 'pendiente')) {
            'pendiente'  => '<span class="badge badge-warning"><i class="fas fa-clock"></i> Pendiente</span>',
            'en_proceso' => '<span class="badge badge-info"><i class="fas fa-spinner fa-spin"></i> En Proceso</span>',
            'cerrado'    => '<span class="badge badge-success"><i class="fas fa-check-circle"></i> Cerrado</span>',
            'anulado'    => '<span class="badge badge-danger"><i class="fas fa-ban"></i> Anulado</span>',
            default      => '<span class="badge badge-secondary">' . htmlspecialchars($this->estadoinventario) . '</span>',
        };
    }

    public function getBadgeClaseEstado(): string
    {
        return match (strtolower($this->getRawOriginal('estadoinventario') ?? 'pendiente')) {
            'pendiente'  => 'warning',
            'en_proceso' => 'info',
            'cerrado'    => 'success',
            'anulado'    => 'danger',
            default      => 'secondary',
        };
    }

    public function estaCerrado(): bool
    {
        return strtolower($this->getRawOriginal('estadoinventario') ?? '') === 'cerrado';
    }

    public function estaAnulado(): bool
    {
        return strtolower($this->getRawOriginal('estadoinventario') ?? '') === 'anulado';
    }

    public function puedeEditarse(): bool
    {
        return !$this->estaCerrado() && !$this->estaAnulado();
    }

    // ==================== SCOPES ====================

    public function scopeBuscar($query, string $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $lower = strtolower($termino);
            $q->whereRaw('LOWER(codigoinventario) LIKE ?', ["%{$lower}%"])
              ->orWhereRaw('LOWER(tipoinventario) LIKE ?', ["%{$lower}%"])
              ->orWhereRaw('LOWER(estadoinventario) LIKE ?', ["%{$lower}%"])
              ->orWhereRaw('LOWER(observacion) LIKE ?', ["%{$lower}%"])
              ->orWhere('id_inventario', 'LIKE', "%{$termino}%");
        });
    }

    public function scopePendientes($query)
    {
        return $query->where('estadoinventario', self::ESTADO_PENDIENTE);
    }

    public function scopeEnProceso($query)
    {
        return $query->where('estadoinventario', self::ESTADO_EN_PROCESO);
    }

    public function scopeCerrados($query)
    {
        return $query->where('estadoinventario', self::ESTADO_CERRADO);
    }

    // ==================== LÓGICA DE CONCILIACIÓN ====================

    /**
     * Obtiene los IDs de las áreas asignadas al responsable de este inventario.
     */
    public function getAreasResponsableIds(): array
    {
        if (!$this->responsable) return [];
        return ResponsableArea::where('dni_responsable', $this->responsable)
                              ->pluck('idarea')
                              ->toArray();
    }

    /**
     * Obtiene los IDs de los bienes que se "esperan" encontrar en este inventario.
     * (Bienes activos cuyo último movimiento apunta a un área del responsable).
     */
    public function getBienesEsperadosIds(): array
    {
        $areas = $this->getAreasResponsableIds();
        if (empty($areas)) return [];

        $ubicaciones = Ubicacion::whereIn('idarea', $areas)->pluck('id_ubicacion')->toArray();
        if (empty($ubicaciones)) return [];

        // Lógica base: Bienes que deberían estar en estas ubicaciones
        $query = Bien::query();

        // 1. Filtrar por estado según el TIPO de inventario
        if ($this->getRawOriginal('tipoinventario') === self::TIPO_BAJA) {
            // El inventario de baja busca bienes que YA están de baja o propuestos para ella
            $query->where(function($q) {
                $q->where('activo', false)
                  ->orWhereHas('estadoBien', fn($sq) => $sq->whereRaw("LOWER(nombre_estado) LIKE '%baja%'"));
            });
        } else {
            // Inventarios estándar buscan solo bienes ACTIVOS y que no estén en estado de BAJA
            $query->where('activo', true)
                  ->where(function($q) {
                      $q->whereHas('estadoBien', function($sq) {
                          $sq->whereRaw("LOWER(nombre_estado) NOT LIKE '%baja%'");
                      })->orWhereDoesntHave('estadoBien');
                  });
        }

        // 2. Filtrar por ubicación actual (último movimiento)
        return $query->whereIn('id_bien', function($sub) use ($ubicaciones) {
                       $sub->select('idbien')
                           ->from('movimiento as m_ext')
                           ->whereIn('m_ext.idubicacion', $ubicaciones)
                           ->where('m_ext.anulado', false)
                           ->where('m_ext.revertido', false)
                           ->whereIn('m_ext.id_movimiento', function($sub2) {
                               $sub2->selectRaw('MAX(m_int.id_movimiento)')
                                    ->from('movimiento as m_int')
                                    ->whereRaw('m_int.idbien = m_ext.idbien')
                                    ->where('m_int.anulado', false)
                                    ->where('m_int.revertido', false);
                           });
                   })
                   ->pluck('id_bien')
                   ->toArray();
    }

    /**
     * Obtiene los IDs de los bienes que ya han sido verificados (registrados) en este inventario.
     */
    public function getBienesVerificadosIds(): array
    {
        // Los bienes se vinculan a través de los movimientos registrados en el detalle
        $movimientosIds = $this->detalles()->pluck('id_movimiento')->toArray();
        if (empty($movimientosIds)) return [];

        return Movimiento::whereIn('id_movimiento', $movimientosIds)
                         ->pluck('idbien')
                         ->unique()
                         ->toArray();
    }

    /**
     * Retorna las estadísticas completas de la conciliación.
     */
    public function getEstadisticasConciliacion(): array
    {
        $esperadosIds = $this->getBienesEsperadosIds();
        $verificadosIds = $this->getBienesVerificadosIds();

        // Faltantes = Esperados que NO han sido verificados
        $faltantesIds = array_diff($esperadosIds, $verificadosIds);

        // Sobrantes = Verificados que NO eran esperados (de otra área)
        $sobrantesIds = array_diff($verificadosIds, $esperadosIds);

        $totalEsperados = count($esperadosIds);
        $totalVerificados = count($verificadosIds);
        
        // El progreso se basa en cuántos de los ESPERADOS hemos encontrado.
        // Verificados conformes = Verificados que SÍ eran esperados
        $verificadosConformes = count(array_intersect($esperadosIds, $verificadosIds));

        $progreso = $totalEsperados > 0 ? round(($verificadosConformes / $totalEsperados) * 100, 1) : 0;
        // Si no hay esperados pero se registraron cosas, progreso es 100% (o 0 si prefieres, usemos 100% visualmente)
        if ($totalEsperados === 0 && $totalVerificados > 0) {
            $progreso = 100;
        }

        return [
            'total_esperados'        => $totalEsperados,
            'total_verificados'      => $totalVerificados,
            'total_faltantes'        => count($faltantesIds),
            'total_sobrantes'        => count($sobrantesIds),
            'verificados_conformes'  => $verificadosConformes,
            'progreso_porcentaje'    => $progreso,
            'esperados_ids'          => $esperadosIds,
            'faltantes_ids'          => array_values($faltantesIds),
            'sobrantes_ids'          => array_values($sobrantesIds),
        ];
    }
}
