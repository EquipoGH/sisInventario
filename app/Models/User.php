<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'dni_usuario',           // ✅ NUEVO
        'rol_usuario',           // ✅ NUEVO
        'estado_usuario',        // ✅ NUEVO
        'ultimo_acceso',         // ✅ NUEVO
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
        'ultimo_acceso' => 'datetime',  // ✅ NUEVO
    ];

    // ✅ RELACIONES CON SISTEMA DE PERMISOS
    /**
     * Relación muchos a muchos con Perfil
     */
    public function perfiles()
    {
        return $this->belongsToMany(Perfil::class, 'usuario_perfil', 'idusuario', 'idperfil');
    }

    /**
     * Relación uno a muchos con Historial
     */
    public function historial()
    {
        return $this->hasMany(Historial::class, 'id_usuario', 'id');
    }

    // ✅ MÉTODOS HELPER PARA PERMISOS (OPCIONAL PERO RECOMENDADO)
    /**
     * Verificar si el usuario tiene un perfil específico
     */
    public function tienePerfil($nombrePerfil)
    {
        return $this->perfiles()->where('nomperfil', $nombrePerfil)->exists();
    }

    /**
     * Verificar si el usuario tiene acceso a un módulo
     */
    public function tieneAccesoModulo($nombreModulo)
    {
        return $this->perfiles()
            ->whereHas('modulos', function ($query) use ($nombreModulo) {
                $query->where('nommodulo', $nombreModulo);
            })
            ->exists();
    }

    /**
     * Obtener todos los módulos del usuario
     */
    public function obtenerModulos()
    {
        return $this->perfiles()
            ->with('modulos')
            ->get()
            ->pluck('modulos')
            ->flatten()
            ->unique('idmodulo');
    }
}
