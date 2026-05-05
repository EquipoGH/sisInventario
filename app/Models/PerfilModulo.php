<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\Pivot;

class PerfilModulo extends Pivot
{
    protected $table = 'perfil_modulo';
    protected $primaryKey = 'idperfilmodulo';

    public $incrementing = true;
    protected $keyType = 'int';
    public $timestamps = true;

    protected $fillable = [
        'idperfil',
        'idmodulo',
    ];

    protected $casts = [
        'idperfilmodulo' => 'integer',
        'idperfil'       => 'integer',
        'idmodulo'       => 'integer',
    ];

    public function perfil(): BelongsTo
    {
        return $this->belongsTo(Perfil::class, 'idperfil', 'idperfil');
    }

    public function modulo(): BelongsTo
    {
        return $this->belongsTo(Modulo::class, 'idmodulo', 'idmodulo');
    }

    public function moduloPermisos(): HasMany
    {
        return $this->hasMany(ModuloPermiso::class, 'idperfilmodulo', 'idperfilmodulo');
    }

    public function permisos(): BelongsToMany
    {
        return $this->belongsToMany(
            Permiso::class,
            'modulo_permisos',
            'idperfilmodulo', // FK en modulo_permisos que apunta a perfil_modulo
            'idpermiso',      // FK en modulo_permisos que apunta a permisos
            'idperfilmodulo', // PK local en perfil_modulo
            'idpermiso'       // PK en permisos
        )->withTimestamps();
    }
}
