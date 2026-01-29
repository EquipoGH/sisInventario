<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use App\Models\Perfil;
use Illuminate\Support\Facades\DB;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'dni_usuario',
        'rol_usuario',
        'estado_usuario',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ultimo_acceso' => 'datetime',
    ];

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPasswordNotification($token));
    }

    public function perfiles()
    {
        return $this->belongsToMany(
            Perfil::class,
            'usuario_perfil',
            'idusuario',
            'idperfil',
            'id',
            'idperfil'
        )->withTimestamps();
    }

    public function historial()
    {
        return $this->hasMany(Historial::class, 'id_usuario', 'id');
    }

    public function tienePerfil($nombrePerfil)
    {
        return $this->perfiles()->where('nomperfil', $nombrePerfil)->exists();
    }

    public function tieneAccesoModulo($nombreModulo)
    {
        return $this->perfiles()
            ->whereHas('modulos', function ($query) use ($nombreModulo) {
                $query->where('nommodulo', $nombreModulo);
            })
            ->exists();
    }

    public function obtenerModulos()
    {
        return $this->perfiles()
            ->with('modulos')
            ->get()
            ->pluck('modulos')
            ->flatten()
            ->unique('idmodulo');
    }

    public function tienePermiso(string $permisoNombre): bool
    {
        return DB::table('usuario_perfil as up')
            ->join('perfil_modulo as pm', 'pm.idperfil', '=', 'up.idperfil')
            ->join('modulo_permisos as mp', 'mp.idperfilmodulo', '=', 'pm.idperfilmodulo')
            ->join('permisos as p', 'p.idpermiso', '=', 'mp.idpermiso')
            ->where('up.idusuario', $this->id)
            ->whereRaw('upper(p.nombpermiso) = upper(?)', [$permisoNombre])
            ->exists();
    }
}
