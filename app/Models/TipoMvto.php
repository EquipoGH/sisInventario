<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class TipoMvto extends Model
{
    use HasFactory;

    protected $table = 'tipo_mvto';
    protected $primaryKey = 'id_tipo_mvto';
    public $incrementing = true;

    protected $fillable = [
        'tipo_mvto'
    ];

    public function getRouteKeyName()
    {
        return 'id_tipo_mvto';
    }

    protected function tipoMvto(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // ⭐ MÉTODO HELPER PARA OBTENER ID POR NOMBRE
    public static function obtenerIdPorNombre($nombre)
    {
        $tipo = self::where('tipo_mvto', 'ILIKE', $nombre)->first();

        if (!$tipo) {
            throw new \Exception("Tipo de movimiento '{$nombre}' no encontrado");
        }

        return $tipo->id_tipo_mvto;
    }

    public function movimientos()
    {
        return $this->hasMany(Movimiento::class, 'tipo_mvto', 'id_tipo_mvto');
    }
}
