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

    // Accessor para mostrar en mayúsculas
    protected function tipoMvto(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Relación con Movimiento (cuando la implementes)
    // public function movimientos()
    // {
    //     return $this->hasMany(Movimiento::class, 'tipo_mvto', 'id_tipo_mvto');
    // }
}
