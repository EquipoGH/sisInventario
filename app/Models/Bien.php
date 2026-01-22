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

    protected $fillable = [
        'codigo_patrimonial',
        'denominacion_bien',
        'id_tipobien',
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

    // Variable temporal para almacenar valores originales
    public $valoresOriginales = [];

    public function getRouteKeyName()
    {
        return 'id_bien';
    }

    // ==================== ACCESSORS ====================

    /**
     * Accessor para mostrar denominación en mayúsculas
     */
    protected function denominacionBien(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
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
     * Relación con Movimientos (NUEVO)
     * Un bien puede tener múltiples movimientos
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
}
