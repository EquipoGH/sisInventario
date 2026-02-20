<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ModuloPermiso extends Model
{
    protected $table = 'modulo_permisos';
    protected $primaryKey = 'idmodulopermiso';

    public $timestamps = true;
    protected $keyType = 'int';
    public $incrementing = true;

    protected $fillable = [
        'idperfilmodulo',
        'idpermiso',
    ];

    protected $casts = [
        'idmodulopermiso' => 'integer',
        'idperfilmodulo'  => 'integer',
        'idpermiso'       => 'integer',
    ];
    protected function color(): \Illuminate\Database\Eloquent\Casts\Attribute
{
    return \Illuminate\Database\Eloquent\Casts\Attribute::make(
        get: fn ($value) => trim((string) $value),
        set: fn ($value) => trim((string) $value),
    );
}

    // Relaciones
    public function perfilModulo(): BelongsTo
    {
        return $this->belongsTo(PerfilModulo::class, 'idperfilmodulo', 'idperfilmodulo');
    }

    public function permiso(): BelongsTo
    {
        return $this->belongsTo(Permiso::class, 'idpermiso', 'idpermiso');
    }
}
