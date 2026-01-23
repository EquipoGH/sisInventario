<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'idpermiso';
    public $timestamps = true; // porque en migración usaste timestamps() [web:136]

    protected $fillable = [
        'nombpermiso',
        'estadopermiso',
    ];

    protected $casts = [
        'idpermiso' => 'integer',
        'nombpermiso' => 'string',
        'estadopermiso' => 'string',
    ];

    // Mutator: normaliza nombre al guardar (columna: nombpermiso)
    protected function nombpermiso(): Attribute
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

    // Estado: A/I (Activo/Inactivo) según migración (default A)
    protected function estadopermiso(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => (string) $value,
            set: function ($value) {
                $v = strtoupper(trim((string) $value));
                return in_array($v, ['A', 'I'], true) ? $v : 'A';
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
            $q->where('nombpermiso', $op, "%{$term}%");
        });
    }

    public function scopeOrdered(Builder $query): Builder
    {
        return $query->orderBy('nombpermiso');
    }

    public function scopeActive(Builder $query): Builder
    {
        return $query->where('estadopermiso', 'A');
    }
}
