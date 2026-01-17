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

    // Accessor para mostrar en mayúsculas (Laravel 9+)
    protected function nombreTipo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Si usas Laravel 8 o anterior, usa este formato:
    // public function getNombreTipoAttribute($value)
    // {
    //     return strtoupper($value);
    // }

    public function bienes()
    {
        return $this->hasMany(Bien::class, 'id_tipobien', 'id_tipo_bien');
    }
}
