<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class EstadoBien extends Model
{
    use HasFactory;

    protected $table = 'estado_bien';
    protected $primaryKey = 'id_estado';
    public $incrementing = true;

    protected $fillable = [
        'nombre_estado'
    ];

    public function getRouteKeyName()
    {
        return 'id_estado';
    }

    protected function nombreEstado(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // ⭐ MÉTODO HELPER PARA OBTENER ID POR NOMBRE
    public static function obtenerIdPorNombre($nombre)
    {
        $estado = self::where('nombre_estado', 'ILIKE', $nombre)->first();

        if (!$estado) {
            throw new \Exception("Estado '{$nombre}' no encontrado");
        }

        return $estado->id_estado;
    }

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'id_estado_bien', 'id_estado');
    }
}
