<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Bien extends Model
{
    use HasFactory;

    protected $table = 'bien';
    protected $primaryKey = 'id_bien';
    public $incrementing = true;
    public $timestamps = true;

    // ⭐ FILLABLE
    protected $fillable = [
        'codigo_patrimonial',
        'denominacion_bien',
        'id_tipobien',
        'id_documento',
        'NumDoc',
        'modelo_bien',
        'marca_bien',
        'color_bien',
        'dimensiones_bien',
        'nserie_bien',
        'fecha_registro',
        'foto_bien',

        // Estado administrativo del bien (Activo, Baja, Prestado, Mantenimiento)
        'id_estado_bien',

        // Condición física actual del bien (Bueno, Regular, Malo, Chatarra)
        'id_estado_conservacion',

        // ⭐ Eliminación lógica (se mantiene por compatibilidad)
        'activo',
        'eliminado_en',

        // ⭐⭐⭐ Usuario que registró el bien ⭐⭐⭐
        'registrado_por',
    ];

    protected $casts = [
        'fecha_registro' => 'date',

        // Estado administrativo y conservación
        'id_estado_bien'         => 'integer',
        'id_estado_conservacion' => 'integer',

        // Campo heredado
        'activo'      => 'boolean',
        'eliminado_en' => 'datetime',
    ];

    protected $appends = [];

    public $valoresOriginales = [];

    public function getRouteKeyName()
    {
        return 'id_bien';
    }

    // ==================== ACCESSORS ====================

    protected function denominacionBien(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    protected function numDoc(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                if (!empty($value)) {
                    return $value;
                }

                if (
                    $this->id_documento &&
                    $this->relationLoaded('documentoSustento') &&
                    $this->documentoSustento
                ) {
                    return $this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento;
                }

                return null;
            }
        );
    }

    // ==================== RELACIONES ====================

    public function tipoBien()
    {
        return $this->belongsTo(TipoBien::class, 'id_tipobien', 'id_tipo_bien');
    }

    /**
     * Estado ADMINISTRATIVO del bien
     * (Activo, Baja, Prestado, Mantenimiento)
     * FK: bien.id_estado_bien → estado_bien.id_estado
     */
    public function estadoBien()
    {
        return $this->belongsTo(EstadoBien::class, 'id_estado_bien', 'id_estado');
    }

    /**
     * Condición FÍSICA actual del bien
     * (Bueno, Regular, Malo, Chatarra)
     * FK: bien.id_estado_conservacion → estado_conservacion.id_estado_conservacion
     */
    public function estadoConservacion()
    {
        return $this->belongsTo(EstadoConservacion::class, 'id_estado_conservacion', 'id_estado_conservacion');
    }

    /**
     * Registro formal de baja del bien (0..1)
     * FK: baja.id_bien → bien.id_bien (UNIQUE)
     */
    public function baja(): HasOne
    {
        return $this->hasOne(Baja::class, 'id_bien', 'id_bien');
    }

    public function documentoSustento()
    {
        return $this->belongsTo(DocumentoSustento::class, 'id_documento', 'id_documento');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'idbien', 'id_bien');
    }

    /**
     * ⭐⭐⭐ NUEVO: Usuario que registró el bien ⭐⭐⭐
     */
    public function registradoPor()
    {
        return $this->belongsTo(User::class, 'registrado_por', 'id');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    public function ultimoMovimiento()
    {
        return $this->movimientos()
            ->orderBy('fecha_mvto', 'desc')
            ->first();
    }

    public function historialMovimientos()
    {
        return $this->movimientos()
            ->with(['tipoMovimiento', 'usuario', 'ubicacion', 'estadoConservacion'])
            ->orderBy('fecha_mvto', 'desc')
            ->get();
    }

    public function cantidadMovimientos()
    {
        return $this->movimientos()->count();
    }

    public function tieneMovimientos()
    {
        return $this->movimientos()->exists();
    }

    public function sincronizarNumDoc()
    {
        if ($this->id_documento) {
            $documento = DocumentoSustento::find($this->id_documento);

            if ($documento) {
                $this->NumDoc = $documento->NumDoc ?? $documento->numero_documento;
                $this->saveQuietly();
                return true;
            }
        }

        return false;
    }

    public function informacionDocumento()
    {
        if (!$this->documentoSustento) {
            return null;
        }

        return [
            'tipo' => $this->documentoSustento->tipo_documento,
            'numero' => $this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento,
            'fecha' => $this->documentoSustento->fecha_documento,
            'descripcion' => "{$this->documentoSustento->tipo_documento} - " .
                ($this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento),
        ];
    }

    // ==================== ELIMINACIÓN LÓGICA ====================

    /**
     * Dar de baja lógicamente (compatibilidad heredada).
     * Usa darDeBaja() para el flujo formal con la tabla baja.
     */
    public function eliminarLogico(): bool
    {
        return $this->forceFill([
            'activo'         => false,
            'eliminado_en'   => now(),
            'id_estado_bien' => EstadoBien::obtenerIdPorNombreNullable(EstadoBien::BAJA),
        ])->save();
    }

    public function restaurar(): bool
    {
        return $this->forceFill([
            'activo'         => true,
            'eliminado_en'   => null,
            'id_estado_bien' => EstadoBien::obtenerIdPorNombreNullable(EstadoBien::ACTIVO),
        ])->save();
    }

    /**
     * Verificar si el bien está dado de baja (por estado administrativo o flag heredado)
     */
    public function estaDadoDeBaja(): bool
    {
        if ($this->estadoBien) {
            return strtolower(trim($this->estadoBien->nombre_estado)) === 'baja';
        }
        return !$this->activo;
    }

    /**
     * Verificar si el bien está activo
     */
    public function estaActivo(): bool
    {
        return !$this->estaDadoDeBaja();
    }

    // ==================== SCOPES ====================

    public function scopeActivos($query)
    {
        return $query->where('activo', true);
    }

    public function scopeEliminados($query)
    {
        return $query->where('activo', false);
    }

    public function scopeConDocumento($query)
    {
        return $query->whereNotNull('id_documento');
    }

    public function scopeSinDocumento($query)
    {
        return $query->whereNull('id_documento');
    }

    public function scopePorNumeroDocumento($query, $numeroDocumento)
    {
        $termLower = strtolower($numeroDocumento);
        return $query->whereRaw('LOWER(NumDoc) LIKE ?', ["%{$termLower}%"]);
    }

    public function scopeBuscar($query, $termino)
    {
        $termLower = strtolower($termino);
        return $query->where(function ($q) use ($termLower) {
            $q->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$termLower}%"])
                ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$termLower}%"])
                ->orWhereRaw('LOWER(marca_bien) LIKE ?', ["%{$termLower}%"])
                ->orWhereRaw('LOWER(modelo_bien) LIKE ?', ["%{$termLower}%"])
                ->orWhereRaw('LOWER(NumDoc) LIKE ?', ["%{$termLower}%"]);
        });
    }

    public function toArrayConRelaciones()
    {
        return [
            'id_bien' => $this->id_bien,
            'codigo_patrimonial' => $this->codigo_patrimonial,
            'denominacion_bien' => $this->denominacion_bien,
            'id_tipobien' => $this->id_tipobien,
            'modelo_bien' => $this->modelo_bien,
            'marca_bien' => $this->marca_bien,
            'color_bien' => $this->color_bien,
            'dimensiones_bien' => $this->dimensiones_bien,
            'nserie_bien' => $this->nserie_bien,
            'fecha_registro' => $this->fecha_registro,
            'foto_bien' => $this->foto_bien,
            'NumDoc' => $this->NumDoc,

            // ⭐ útil para UI
            'activo' => $this->activo,
            'eliminado_en' => $this->eliminado_en,

            'tipo_bien' => $this->tipoBien ? [
                'id_tipo_bien' => $this->tipoBien->id_tipo_bien,
                'nombre_tipo' => $this->tipoBien->nombre_tipo,
            ] : null,
            'documento_sustento' => $this->documentoSustento ? [
                'id_documento' => $this->documentoSustento->id_documento,
                'tipo_documento' => $this->documentoSustento->tipo_documento,
                'NumDoc' => $this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento,
            ] : null,
        ];
    }

    public function latestMovimiento(): HasOne
    {
        // Último por fecha_mvto y desempate por id_movimiento
        return $this->hasOne(Movimiento::class, 'idbien', 'id_bien')
            ->latestOfMany(['fecha_mvto', 'id_movimiento']);
    }

    // ==================== LÓGICA DE INVENTARIO ====================

    /**
     * Verifica si el bien pertenece a algún inventario actualmente en proceso.
     */
    public function estaEnInventarioActivo(): bool
    {
        return $this->getInventariosActivos()->isNotEmpty();
    }

    /**
     * Obtiene los inventarios "En Proceso" que deberían incluir este bien.
     */
    public function getInventariosActivos()
    {
        $idBien = $this->id_bien;

        // 1. Buscar inventarios donde este bien ya haya sido VERIFICADO (esté en detalles)
        $inventariosPorDetalle = Inventario::where(function($q) {
                $q->where('estadoinventario', 'en_proceso')
                  ->orWhere('estadoinventario', 'pendiente');
            })
            ->whereIn('id_inventario', function($query) use ($idBien) {
                $query->select('id_inventario')
                    ->from('detalle_inventario')
                    ->join('movimiento', 'detalle_inventario.id_movimiento', '=', 'movimiento.id_movimiento')
                    ->where('movimiento.idbien', $idBien);
            })
            ->get();

        // 2. Buscar inventarios donde el bien esté en el SCOPE (área del responsable)
        $ultimoMovimiento = $this->latestMovimiento;
        $idArea = null;
        if ($ultimoMovimiento && $ultimoMovimiento->idubicacion) {
            // Cargar ubicación si no está cargada
            $ubicacion = $ultimoMovimiento->ubicacion;
            if (!$ubicacion) {
                $ubicacion = Ubicacion::find($ultimoMovimiento->idubicacion);
            }
            $idArea = $ubicacion ? $ubicacion->idarea : null;
        }

        $inventariosPorScope = collect();
        if ($idArea) {
            $inventariosPorScope = Inventario::where(function($q) {
                    $q->where('estadoinventario', 'en_proceso')
                      ->orWhere('estadoinventario', 'pendiente');
                })
                ->whereIn('responsable', function($query) use ($idArea) {
                    $query->select('dni_responsable')
                        ->from('responsable_area')
                        ->where('idarea', $idArea);
                })
                ->get();
        }

        // Combinar y quitar duplicados
        return $inventariosPorDetalle->concat($inventariosPorScope)->unique('id_inventario');
    }
}
