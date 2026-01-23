<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';
    protected $primaryKey = 'idmodulo';
    public $timestamps = true; // tu migración tiene timestamps() [web:139]

    protected $fillable = [
        'nommodulo',
        'estadomodulo',
        'etiqueta',
        'color',
    ];

    protected $casts = [
        'idmodulo' => 'integer',
        'nommodulo' => 'string',
        'estadomodulo' => 'string',
        'etiqueta' => 'string',
        'color' => 'string',
    ];

    // Normaliza nombre
    protected function nommodulo(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => (string) $value,
            set: function ($value) {
                $value = trim((string) $value);
                $value = preg_replace('/\s+/', ' ', $value);
                return mb_convert_case($value, MB_CASE_TITLE, 'UTF-8');
            }
        );
    }

    // Estado A/I
    protected function estadomodulo(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (string) $value,
            set: function ($value) {
                $v = strtoupper(trim((string) $value));
                return in_array($v, ['A', 'I'], true) ? $v : 'A';
            }
        );
    }

    // Etiqueta: trim, null si vacío
    protected function etiqueta(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : (string) $value,
            set: function ($value) {
                $v = trim((string) $value);
                return $v === '' ? null : $v;
            }
        );
    }

    // Color: trim, null si vacío
    protected function color(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : (string) $value,
            set: function ($value) {
                $v = trim((string) $value);
                return $v === '' ? null : $v;
            }
        );
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;

        $driver = $query->getConnection()->getDriverName();
        $op = $driver === 'pgsql' ? 'ilike' : 'like';

        return $query->where(function (Builder $q) use ($term, $op) {
            $q->where('nommodulo', $op, "%{$term}%")
              ->orWhere('etiqueta', $op, "%{$term}%")
              ->orWhere('color', $op, "%{$term}%")
              ->orWhere('idmodulo', $op, "%{$term}%");
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('nommodulo');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('estadomodulo', 'A');
    }
}
