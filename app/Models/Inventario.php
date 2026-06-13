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

    // Tags de Alcance (para usar en el campo observación sin migraciones)
    const TAG_ALCANCE_GENERAL = '[ALCANCE_GENERAL]';
    const TAG_ALCANCE_AREA    = '[ALCANCE_AREA:'; // Se completa con ID y ']'
    const TAG_ALCANCE_UBICACION = '[ALCANCE_UBICACION:'; // Se completa con ID y ']'

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

    public function incidencias()
    {
        return $this->hasMany(Incidencia::class, 'id_inventario', 'id_inventario');
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
            'pendiente'  => '<span class="badge badge-warning" id="estado_actual_badge"><i class="fas fa-clock"></i> Pendiente</span>',
            'en_proceso' => '<span class="badge badge-info" id="estado_actual_badge"><i class="fas fa-spinner fa-spin"></i> En Proceso</span>',
            'cerrado'    => '<span class="badge badge-success" id="estado_actual_badge"><i class="fas fa-check-circle"></i> Cerrado</span>',
            'anulado'    => '<span class="badge badge-danger" id="estado_actual_badge"><i class="fas fa-ban"></i> Anulado</span>',
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
     * Soporta alcance por Responsable, Área específica o General.
     */
    public function getBienesEsperadosIds(): array
    {
        $obs = $this->observacion ?? '';

        // 1. ALCANCE GENERAL [ALCANCE_GENERAL]
        if (str_contains($obs, self::TAG_ALCANCE_GENERAL)) {
            return $this->getBienesPorFiltroUbicacion(null);
        }

        // 2. ALCANCE POR UBICACIÓN ESPECÍFICA [ALCANCE_UBICACION:ID]
        if (preg_match('/\[ALCANCE_UBICACION:(\d+)\]/', $obs, $matches)) {
            $idUbicacion = $matches[1];
            return $this->getBienesPorFiltroUbicacion([$idUbicacion]);
        }

        // 3. ALCANCE POR ÁREA ESPECÍFICA [ALCANCE_AREA:ID]
        if (preg_match('/\[ALCANCE_AREA:(\d+)\]/', $obs, $matches)) {
            $idArea = $matches[1];
            $ubicaciones = Ubicacion::where('idarea', $idArea)->pluck('id_ubicacion')->toArray();
            return $this->getBienesPorFiltroUbicacion($ubicaciones);
        }

        // 4. ALCANCE POR RESPONSABLE (Bienes en áreas donde él es jefe)
        $areas = $this->getAreasResponsableIds();
        if (empty($areas)) return [];
        $ubicaciones = Ubicacion::whereIn('idarea', $areas)->pluck('id_ubicacion')->toArray();
        return $this->getBienesPorFiltroUbicacion($ubicaciones);
    }

    /**
     * Motor de búsqueda: Filtra bienes basándose estrictamente en su ÚLTIMO movimiento vigente.
     */
    private function getBienesPorFiltroUbicacion(?array $ubicacionesIds = null): array
    {
        $query = Bien::query();

        // Filtro por tipo de inventario (Activos vs Bajas)
        if ($this->getRawOriginal('tipoinventario') === self::TIPO_BAJA) {
            $query->where(function($q) {
                $q->where('activo', false)
                  ->orWhereHas('estadoBien', fn($sq) => $sq->whereRaw("LOWER(nombre_estado) LIKE '%baja%'"));
            });
        } else {
            $query->where('activo', true)
                  ->where(function($q) {
                      $q->whereHas('estadoBien', function($sq) {
                          $sq->whereRaw("LOWER(nombre_estado) NOT LIKE '%baja%'");
                      })->orWhereDoesntHave('estadoBien');
                  });
        }

        return $query->whereIn('id_bien', function($sub) use ($ubicacionesIds) {
            $sub->select('idbien')
                ->from('movimiento as m_ext')
                ->where('m_ext.anulado', false)
                ->where('m_ext.revertido', false)
                // Asegurar que sea el ÚLTIMO movimiento de este bien
                ->whereRaw('m_ext.id_movimiento = (
                    select MAX(m_int.id_movimiento) 
                    from movimiento as m_int 
                    where m_int.idbien = m_ext.idbien 
                    and m_int.anulado = false 
                    and m_int.revertido = false
                )')
                // Aplicar filtro de ubicación si existe
                ->when($ubicacionesIds, function($q) use ($ubicacionesIds) {
                    $q->whereIn('m_ext.idubicacion', $ubicacionesIds);
                });
        })->pluck('id_bien')->toArray();
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
        
        // Verificados Reales (Encontrados físicamente: Conformes u Observados)
        $verificadosIds = $this->detalles()
            ->whereIn('estadoverificacion', [\App\Models\DetalleInventario::VERIFICADO, \App\Models\DetalleInventario::OBSERVADO])
            ->with('movimiento')
            ->get()
            ->pluck('movimiento.idbien')
            ->unique()
            ->toArray();

        // Confirmados como No Encontrados (Perdidos)
        $perdidosIds = $this->detalles()
            ->where('estadoverificacion', \App\Models\DetalleInventario::NO_ENCONTRADO)
            ->with('movimiento')
            ->get()
            ->pluck('movimiento.idbien')
            ->unique()
            ->toArray();

        // Faltantes = Esperados que NO han sido verificados ni confirmados como perdidos
        $faltantesIds = array_diff($esperadosIds, $verificadosIds, $perdidosIds);

        // Sobrantes = Verificados que NO eran esperados (de otra área)
        $sobrantesIds = array_diff($verificadosIds, $esperadosIds);

        $totalEsperados = count($esperadosIds);
        $totalVerificados = count($verificadosIds);
        $totalPerdidos = count($perdidosIds);
        
        // Verificados conformes = Verificados que SÍ eran esperados
        $verificadosConformes = count(array_intersect($esperadosIds, $verificadosIds));

        // El progreso real se basa en cuántos de los ESPERADOS han sido PROCESADOS (encontrados o confirmados perdidos)
        $procesados = count(array_intersect($esperadosIds, array_merge($verificadosIds, $perdidosIds)));
        $progreso = $totalEsperados > 0 ? round(($procesados / $totalEsperados) * 100, 1) : 0;

        if ($totalEsperados === 0 && $totalVerificados > 0) {
            $progreso = 100;
        }

        return [
            'total_esperados'        => $totalEsperados,
            'total_verificados'      => $totalVerificados, // Encontrados
            'total_faltantes'        => count($faltantesIds), // Aún sin procesar
            'total_perdidos'         => $totalPerdidos,      // Procesados como no encontrados
            'total_sobrantes'        => count($sobrantesIds),
            'verificados_conformes'  => $verificadosConformes,
            'progreso_porcentaje'    => $progreso,
            'esperados_ids'          => $esperadosIds,
            'faltantes_ids'          => array_values($faltantesIds),
            'perdidos_ids'           => array_values($perdidosIds),
            'sobrantes_ids'          => array_values($sobrantesIds),
        ];
    }

    /**
     * Retorna el tipo de alcance en texto legible.
     */
    public function getAlcanceHumanizado()
    {
        $obs = $this->observacion ?? '';
        if (str_contains($obs, '[ALCANCE_GENERAL]')) {
            return 'General';
        }
        if (preg_match('/\[ALCANCE_UBICACION:(\d+)\]/', $obs, $matches)) {
            $ubicacion = \App\Models\Ubicacion::find($matches[1]);
            return 'Ubicación: ' . ($ubicacion ? $ubicacion->ambiente : "ID {$matches[1]}");
        }
        if (preg_match('/\[ALCANCE_AREA:(\d+)\]/', $obs, $matches)) {
            $area = \App\Models\Area::find($matches[1]);
            return 'Área: ' . ($area->nombre_area ?? "ID {$matches[1]}");
        }
        return 'Por Responsable';
    }

    /**
     * Retorna un badge HTML con el tipo de alcance.
     */
    public function getAlcanceBadge()
    {
        $alcance = $this->getAlcanceHumanizado();
        $clase = 'badge-secondary';
        $icon = 'fa-user';

        if (str_contains($alcance, 'General')) {
            $clase = 'badge-purple text-white';
            $icon = 'fa-globe';
        } elseif (str_contains($alcance, 'Ubicación') || str_contains($alcance, 'Área')) {
            $clase = 'badge-primary';
            $icon = 'fa-map-marker-alt';
        }

        $style = str_contains($alcance, 'General') ? 'style="background-color: #6f42c1;"' : '';
        
        return "<span class='badge {$clase}' {$style}><i class='fas {$icon}'></i> {$alcance}</span>";
    }
}
