<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';
    protected $primaryKey = 'idmodulo';
    public $timestamps = true;

    protected $fillable = [
        'nommodulo',
        'estadomodulo',
        'etiqueta',
        'color',
        'icono',
        'route_prefix',
    ];

    protected $casts = [
        'idmodulo' => 'integer',
        'nommodulo' => 'string',
        'estadomodulo' => 'string',
        'etiqueta' => 'string',
        'color' => 'string',
        'icono' => 'string',
        'route_prefix' => 'string',
    ];

    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'perfil_modulo', 'idmodulo', 'idperfil')
            ->using(PerfilModulo::class)
            ->withPivot('idperfilmodulo')
            ->withTimestamps();
    }

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

    protected function icono(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : (string) $value,
            set: function ($value) {
                $v = trim((string) $value);
                $v = preg_replace('/\s+/', ' ', $v);
                return $v === '' ? null : $v;
            }
        );
    }

    protected function routePrefix(): Attribute
    {
        return Attribute::make(
            get: fn ($value) => $value === null ? null : (string) $value,
            set: function ($value) {
                $v = trim((string) $value);
                $v = preg_replace('/\s+/', '', $v); // lista por coma sin espacios
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
              ->orWhere('icono', $op, "%{$term}%")
              ->orWhere('route_prefix', $op, "%{$term}%");

            if (ctype_digit($term)) {
                $q->orWhere('idmodulo', (int) $term);
            }
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
