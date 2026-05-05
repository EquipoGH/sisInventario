<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TipoBien extends Model
{
    use HasFactory;

    protected $table = 'tipo_bien';
    protected $primaryKey = 'id_tipo_bien';
    public $incrementing = true;

    protected $fillable = [
        'nombre_tipo'
    ];

    public function getRouteKeyName()
    {
        return 'id_tipo_bien';
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor para nombre_tipo en mayúsculas
     */
    protected function nombreTipo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    /**
     * ⭐ NUEVO: Alias para tipo_bien (compatibilidad con código existente)
     * Permite usar $tipoBien->tipo_bien en lugar de $tipoBien->nombre_tipo
     */
    protected function tipoBien(): Attribute
    {
        return Attribute::make(
            get: fn () => strtoupper($this->attributes['nombre_tipo'] ?? ''),
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Un tipo de bien tiene muchos bienes
     */
    public function bienes()
    {
        return $this->hasMany(Bien::class, 'id_tipobien', 'id_tipo_bien');
    }

    // ==================== MÉTODOS AUXILIARES ====================

    /**
     * Cantidad de bienes de este tipo
     */
    public function cantidadBienes()
    {
        return $this->bienes()->where('activo', true)->count();
    }

    /**
     * Verificar si tiene bienes activos
     */
    public function tieneBienes()
    {
        return $this->bienes()->where('activo', true)->exists();
    }
}
