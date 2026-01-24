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
    public $timestamps = true; // ⭐ IMPORTANTE: Si usas created_at/updated_at

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
        'foto_bien'
    ];

    protected $casts = [
        'fecha_registro' => 'date'
    ];

    // ⭐ AGREGAR ESTO - Para incluir relaciones en JSON/Array
    protected $appends = [];

    // ⭐ NO uses $with aquí para evitar cargas innecesarias
    // Solo carga con with() en el controlador cuando lo necesites

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

    // ⭐ CORREGIDO: Accessor simplificado para NumDoc
    // NO lo hagas tan complejo, el NumDoc ya está en la BD
    protected function numDoc(): Attribute
    {
        return Attribute::make(
            get: function ($value) {
                // Si ya tiene valor en la BD, retornarlo
                if (!empty($value)) {
                    return $value;
                }

                // Si tiene documento relacionado, obtenerlo
                if ($this->id_documento && $this->relationLoaded('documentoSustento') && $this->documentoSustento) {
                    return $this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento;
                }

                return null;
            }
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Relación con TipoBien
     */
    public function tipoBien()
    {
        return $this->belongsTo(TipoBien::class, 'id_tipobien', 'id_tipo_bien');
    }

    /**
     * Relación con DocumentoSustento
     */
    public function documentoSustento()
    {
        return $this->belongsTo(DocumentoSustento::class, 'id_documento', 'id_documento');
    }

    /**
     * Relación con Movimientos
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'idbien', 'id_bien');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Obtener el último movimiento del bien
     */
    public function ultimoMovimiento()
    {
        return $this->movimientos()
                    ->orderBy('fecha_mvto', 'desc')
                    ->first();
    }

    /**
     * Obtener historial completo de movimientos
     */
    public function historialMovimientos()
    {
        return $this->movimientos()
                    ->with(['tipoMovimiento', 'usuario', 'ubicacion', 'estadoConservacion'])
                    ->orderBy('fecha_mvto', 'desc')
                    ->get();
    }

    /**
     * Contar movimientos del bien
     */
    public function cantidadMovimientos()
    {
        return $this->movimientos()->count();
    }

    /**
     * Verificar si tiene movimientos
     */
    public function tieneMovimientos()
    {
        return $this->movimientos()->exists();
    }

    /**
     * ⭐ Sincronizar NumDoc desde documento_sustento
     * Este método se llama desde el controlador después de crear/actualizar
     */
    public function sincronizarNumDoc()
    {
        if ($this->id_documento) {
            $documento = DocumentoSustento::find($this->id_documento);

            if ($documento) {
                // Usa NumDoc o numero_documento dependiendo de tu BD
                $this->NumDoc = $documento->NumDoc ?? $documento->numero_documento;
                $this->saveQuietly(); // No dispara eventos
                return true;
            }
        }

        return false;
    }

    /**
     * ⭐ Obtener información completa del documento
     */
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
                           ($this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento)
        ];
    }

    // ==================== SCOPES ====================

    /**
     * Scope: Bienes que tienen documento
     */
    public function scopeConDocumento($query)
    {
        return $query->whereNotNull('id_documento');
    }

    /**
     * Scope: Bienes sin documento
     */
    public function scopeSinDocumento($query)
    {
        return $query->whereNull('id_documento');
    }

    /**
     * Scope: Buscar por número de documento
     */
    public function scopePorNumeroDocumento($query, $numeroDocumento)
    {
        return $query->where('NumDoc', 'ILIKE', "%{$numeroDocumento}%");
    }

    /**
     * ⭐ NUEVO: Scope para búsqueda general
     */
    public function scopeBuscar($query, $termino)
    {
        return $query->where(function($q) use ($termino) {
            $q->where('codigo_patrimonial', 'ILIKE', "%{$termino}%")
              ->orWhere('denominacion_bien', 'ILIKE', "%{$termino}%")
              ->orWhere('marca_bien', 'ILIKE', "%{$termino}%")
              ->orWhere('modelo_bien', 'ILIKE', "%{$termino}%")
              ->orWhere('NumDoc', 'ILIKE', "%{$termino}%");
        });
    }

    /**
     * ⭐ NUEVO: Método para convertir a array con relaciones
     * Este método asegura que las relaciones se incluyan
     */
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
            'tipo_bien' => $this->tipoBien ? [
                'id_tipo_bien' => $this->tipoBien->id_tipo_bien,
                'nombre_tipo' => $this->tipoBien->nombre_tipo
            ] : null,
            'documento_sustento' => $this->documentoSustento ? [
                'id_documento' => $this->documentoSustento->id_documento,
                'tipo_documento' => $this->documentoSustento->tipo_documento,
                'NumDoc' => $this->documentoSustento->NumDoc ?? $this->documentoSustento->numero_documento
            ] : null
        ];
    }
}
