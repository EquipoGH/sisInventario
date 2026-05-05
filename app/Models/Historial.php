<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Historial extends Model
{
    protected $table = 'historial';
    protected $primaryKey = 'id_historial';

    protected $fillable = [
        'fecha_hora_cambio',
        'id_usuario',
        'entidad_afectada',
        'id_registro_afectado',
        'accion',
        'campo_modificado',
        'valor_anterior',
        'valor_nuevo',
        'motivo_cambio',
    ];

    protected $casts = [
        'fecha_hora_cambio' => 'datetime',
    ];

    // Relaciones
    public function usuario()
    {
        return $this->belongsTo(User::class, 'id_usuario', 'id');
    }
}
