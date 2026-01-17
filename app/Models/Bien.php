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

    public function getRouteKeyName()
    {
        return 'id_bien';
    }

    // Accessor para mostrar denominación en mayúsculas
    protected function denominacionBien(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Relación con TipoBien
    public function tipoBien()
    {
        return $this->belongsTo(TipoBien::class, 'id_tipobien', 'id_tipo_bien');
    }
}
