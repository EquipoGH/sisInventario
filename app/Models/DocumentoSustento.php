<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;

class DocumentoSustento extends Model
{
    use HasFactory;

    protected $table = 'documento_sustento';
    protected $primaryKey = 'id_documento';
    public $incrementing = true;

    protected $fillable = [
        'tipo_documento',
        'numero_documento',
        'fecha_documento'
    ];

    protected $casts = [
        'fecha_documento' => 'date'
    ];

    public function getRouteKeyName()
    {
        return 'id_documento';
    }

    // Accessor para tipo de documento en mayúsculas
    protected function tipoDocumento(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => strtoupper($value),
        );
    }

    // Accessor para formatear fecha
    public function getFechaFormateadaAttribute()
    {
        return Carbon::parse($this->fecha_documento)->format('d/m/Y');
    }

    // Relación con Movimiento (cuando la implementes)
    // public function movimientos()
    // {
    //     return $this->hasMany(Movimiento::class, 'id_documento', 'id_documento');
    // }
}
