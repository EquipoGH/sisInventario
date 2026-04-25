<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Baja extends Model
{
    use HasFactory;

    protected $table      = 'baja';
    protected $primaryKey = 'id_baja';
    public $incrementing  = true;
    public $timestamps    = true;

    protected $fillable = [
        'id_bien',
        'fecha_baja',
        'motivo_baja',
        'resolucion',
        'observacion',
    ];

    protected $casts = [
        'fecha_baja' => 'date',
        'id_bien'    => 'integer',
    ];

    public function getRouteKeyName(): string
    {
        return 'id_baja';
    }

    // ==================== RELACIONES ====================

    /**
     * Bien al que pertenece esta baja (relación inversa 1:1)
     */
    public function bien()
    {
        return $this->belongsTo(Bien::class, 'id_bien', 'id_bien');
    }

    // ==================== SCOPES ====================

    public function scopeBuscar($query, string $termino)
    {
        $termLower = strtolower($termino);
        return $query->where(function ($q) use ($termLower) {
            $q->whereRaw('LOWER(motivo_baja) LIKE ?', ["%{$termLower}%"])
              ->orWhereRaw('LOWER(resolucion) LIKE ?', ["%{$termLower}%"])
              ->orWhereHas('bien', function ($q2) use ($termLower) {
                  $q2->whereRaw('LOWER(codigo_patrimonial) LIKE ?', ["%{$termLower}%"])
                     ->orWhereRaw('LOWER(denominacion_bien) LIKE ?', ["%{$termLower}%"]);
              });
        });
    }

    public function scopeEntreFechas($query, $desde, $hasta)
    {
        return $query->whereBetween('fecha_baja', [$desde, $hasta]);
    }
}
