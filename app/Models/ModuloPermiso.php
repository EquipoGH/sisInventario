<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModuloPermiso extends Model
{
    protected $table = 'modulo_permisos';
    protected $primaryKey = 'idmodulopermiso';

    protected $fillable = [
        'idperfilmodulo',
        'idpermiso',
    ];

    // Relaciones
    public function perfilModulo()
    {
        return $this->belongsTo(PerfilModulo::class, 'idperfilmodulo', 'idperfilmodulo');
    }

    public function permiso()
    {
        return $this->belongsTo(Permiso::class, 'idpermiso', 'idpermiso');
    }
}
