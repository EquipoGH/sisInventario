<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EstadoConservacion extends Model
{
    use HasFactory;

    protected $table      = 'estado_conservacion';
    protected $primaryKey = 'id_estado_conservacion';
    public $incrementing  = true;
    public $timestamps    = true;

    protected $fillable = [
        'nombre_conservacion',
    ];

    // Valores canónicos del sistema
    const BUENO    = 'Bueno';
    const REGULAR  = 'Regular';
    const MALO     = 'Malo';
    const CHATARRA = 'Chatarra';

    public function getRouteKeyName(): string
    {
        return 'id_estado_conservacion';
    }

    // ==================== ACCESSORS ====================

    protected function nombreConservacion(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst(strtolower($value)),
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Bienes cuya condición física actual es este estado
     */
    public function bienes()
    {
        return $this->hasMany(Bien::class, 'id_estado_conservacion', 'id_estado_conservacion');
    }

    /**
     * Movimientos registrados con este estado de conservación
     */
    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'id_estado_conservacion_bien', 'id_estado_conservacion');
    }

    // ==================== MÉTODOS HELPER ====================

    /**
     * Obtener ID por nombre (insensible a mayúsculas)
     */
    public static function obtenerIdPorNombre(string $nombre): ?int
    {
        $estado = self::whereRaw('UPPER(TRIM(nombre_conservacion)) = ?', [strtoupper(trim($nombre))])
            ->first();

        return $estado?->id_estado_conservacion;
    }

    /**
     * Obtener el badge CSS según el estado
     */
    public function getBadgeClass(): string
    {
        return match (strtolower(trim($this->nombre_conservacion))) {
            'bueno'    => 'badge-success',
            'regular'  => 'badge-warning',
            'malo'     => 'badge-danger',
            'chatarra' => 'badge-dark',
            default    => 'badge-secondary',
        };
    }

    // ==================== SCOPES ====================

    public function scopeBuscar($query, string $termino)
    {
        return $query->whereRaw('LOWER(nombre_conservacion) LIKE ?', ['%' . strtolower($termino) . '%']);
    }
}
