<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UsuarioPerfil extends Model
{
    protected $table = 'usuario_perfil';
    protected $primaryKey = 'idusuarioperfil';
    public $timestamps = true;

    protected $fillable = ['idusuario','idperfil'];

    public function usuario()
    {
        return $this->belongsTo(User::class, 'idusuario', 'id');
    }

    public function perfil()
    {
        return $this->belongsTo(Perfil::class, 'idperfil', 'idperfil');
    }
}

