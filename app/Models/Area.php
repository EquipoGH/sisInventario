<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;

class Area extends Model
{
    use HasFactory;

    protected $table = 'area';
    protected $primaryKey = 'id_area';
    public $incrementing = true;

    protected $fillable = [
        'nombre_area'
    ];

    public function getRouteKeyName()
    {
        return 'id_area';
    }

    // Accessor para mostrar nombre en mayúsculas
    protected function nombreArea(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Relaciones
    public function ubicaciones()
    {
        return $this->hasMany(Ubicacion::class, 'id_area', 'id_area');
    }

    public function responsables()
    {
        return $this->hasMany(Responsable::class, 'idarea', 'id_area');
    }
}
