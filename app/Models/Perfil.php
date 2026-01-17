<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Perfil extends Model
{
    protected $table = 'perfil';
    protected $primaryKey = 'idperfil';

    protected $fillable = [
        'nomperfil',
    ];

    // Relaciones
    public function usuarios()
    {
        return $this->belongsToMany(User::class, 'usuario_perfil', 'idperfil', 'idusuario');
    }

    public function modulos()
    {
        return $this->belongsToMany(Modulo::class, 'perfil_modulo', 'idperfil', 'idmodulo');
    }
}
