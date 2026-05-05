<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Permiso extends Model
{
    protected $table = 'permisos';
    protected $primaryKey = 'idpermiso';
    public $timestamps = true;

    protected $fillable = [
        'nombpermiso',
        'route_name',     // <-- NUEVO
        'estadopermiso',
    ];

    protected $casts = [
        'idpermiso' => 'integer',
        'nombpermiso' => 'string',
        'route_name' => 'string',    // <-- NUEVO
        'estadopermiso' => 'string',
    ];

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

    protected function routeName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value,
            set: function ($value) {
                $value = trim((string) $value);
                $value = preg_replace('/\s+/', '', $value);
                return $value === '' ? null : $value;
            }
        );
    }

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
            $q->where('nombpermiso', $op, "%{$term}%")
              ->orWhere('route_name', $op, "%{$term}%")
              ->orWhereRaw('CAST(idpermiso AS TEXT) ' . strtoupper($op) . ' ?', ["%{$term}%"]);
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

    public function moduloPermisos()
    {
        return $this->hasMany(ModuloPermiso::class, 'idpermiso', 'idpermiso');
    }
}
