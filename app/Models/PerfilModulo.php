<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PerfilModulo extends Model
{
    protected $table = 'perfil_modulo';
    protected $primaryKey = 'idperfilmodulo';

    protected $fillable = [
        'idperfil',
        'idmodulo',
    ];

    // Relaciones
    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idperfil', 'idperfil');
    }

    public function modulo()
    {
        return $this->belongsTo(Modulo::class, 'idmodulo', 'idmodulo');
    }

    public function permisos()
    {
        return $this->belongsToMany(Permiso::class, 'modulo_permisos', 'idperfilmodulo', 'idpermiso');
    }
}
