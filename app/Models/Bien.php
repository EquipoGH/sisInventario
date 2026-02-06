<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

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

        // ⭐ NUEVO: eliminación lógica
        'activo',
        'eliminado_en',
    ];

    protected $casts = [
        'fecha_registro' => 'date',

        // ⭐ NUEVO
        'activo' => 'boolean',
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

    public function documentoSustento()
    {
        return $this->belongsTo(DocumentoSustento::class, 'id_documento', 'id_documento');
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'idbien', 'id_bien');
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

    public function eliminarLogico(): bool
    {
        return $this->forceFill([
            'activo' => false,
            'eliminado_en' => now(),
        ])->save();
    }

    public function restaurar(): bool
    {
        return $this->forceFill([
            'activo' => true,
            'eliminado_en' => null,
        ])->save();
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
        return $query->where('NumDoc', 'ILIKE', "%{$numeroDocumento}%");
    }

    public function scopeBuscar($query, $termino)
    {
        return $query->where(function ($q) use ($termino) {
            $q->where('codigo_patrimonial', 'ILIKE', "%{$termino}%")
                ->orWhere('denominacion_bien', 'ILIKE', "%{$termino}%")
                ->orWhere('marca_bien', 'ILIKE', "%{$termino}%")
                ->orWhere('modelo_bien', 'ILIKE', "%{$termino}%")
                ->orWhere('NumDoc', 'ILIKE', "%{$termino}%");
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
}
