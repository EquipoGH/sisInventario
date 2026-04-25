<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

/**
 * EstadoBien — Estado ADMINISTRATIVO del bien en el ciclo de vida institucional.
 *
 * Valores: Activo | Baja | Prestado | Mantenimiento
 *
 * NOTA: NO confundir con EstadoConservacion (condición física: Bueno/Regular/Malo/Chatarra).
 */
class EstadoBien extends Model
{
    use HasFactory;

    protected $table      = 'estado_bien';
    protected $primaryKey = 'id_estado';
    public $incrementing  = true;
    public $timestamps    = true;

    // Valores canónicos del sistema
    const ACTIVO       = 'Activo';
    const BAJA         = 'Baja';
    const PRESTADO     = 'Prestado';
    const MANTENIMIENTO = 'Mantenimiento';

    protected $fillable = [
        'nombre_estado',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_estado';
    }

    protected function nombreEstado(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => ucfirst(strtolower($value)),
        );
    }

    // ==================== RELACIONES ====================

    /**
     * Bienes con este estado administrativo
     * FK: bien.id_estado_bien → estado_bien.id_estado
     */
    public function bienes()
    {
        return $this->hasMany(Bien::class, 'id_estado_bien', 'id_estado');
    }

    // ==================== MÉTODOS HELPER ====================

    /**
     * Obtener ID por nombre (insensible a mayúsculas)
     */
    public static function obtenerIdPorNombre(string $nombre): int
    {
        $estado = self::whereRaw('UPPER(TRIM(nombre_estado)) = ?', [strtoupper(trim($nombre))])
            ->first();

        if (!$estado) {
            throw new \Exception("EstadoBien '{$nombre}' no encontrado en la BD.");
        }

        return $estado->id_estado;
    }

    /**
     * Obtener ID por nombre, devuelve null si no existe (no lanza excepción)
     */
    public static function obtenerIdPorNombreNullable(string $nombre): ?int
    {
        return self::whereRaw('UPPER(TRIM(nombre_estado)) = ?', [strtoupper(trim($nombre))])
            ->value('id_estado');
    }

    /**
     * Obtener el badge CSS según el estado administrativo
     */
    public function getBadgeClass(): string
    {
        return match (strtolower(trim($this->nombre_estado))) {
            'activo'       => 'badge-success',
            'baja'         => 'badge-danger',
            'prestado'     => 'badge-info',
            'mantenimiento' => 'badge-warning',
            default        => 'badge-secondary',
        };
    }

    // ==================== SCOPES ====================

    public function scopeBuscar($query, string $termino)
    {
        return $query->whereRaw('LOWER(nombre_estado) LIKE ?', ['%' . strtolower($termino) . '%']);
    }
}
