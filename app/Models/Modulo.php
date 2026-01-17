<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Modulo extends Model
{
    protected $table = 'modulos';
    protected $primaryKey = 'idmodulo';

    protected $fillable = [
        'nommodulo',
        'estadomodulo',
        'etiqueta',
        'color',
    ];

    // Relaciones
    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'perfil_modulo', 'idmodulo', 'idperfil');
    }
}
