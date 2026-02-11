<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Notifications\ResetPasswordNotification;
use App\Models\Perfil;

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

    // ==========================================
    // ⭐⭐⭐ MÉTODOS PARA REVERSIÓN DE BAJAS ⭐⭐⭐
    // ==========================================

    /**
     * Verificar si el usuario es administrador
     * Basado en el campo 'rol_usuario' de la BD
     *
     * @return bool
     */
    public function esAdmin(): bool
    {
        return strtoupper($this->rol_usuario) === 'ADMIN';
    }

    /**
     * Verificar si el usuario puede revertir bajas
     * (Solo administradores pueden revertir)
     *
     * @return bool
     */
    public function puedeRevertirBajas(): bool
    {
        return $this->esAdmin();
    }

    /**
     * Relación: Movimientos revertidos por este usuario
     * (Para auditoría y trazabilidad)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movimientosRevertidos()
    {
        return $this->hasMany(Movimiento::class, 'revertido_por', 'id');
    }

    // ==========================================
    // ⭐⭐⭐ NUEVOS MÉTODOS PARA SOFT DELETE (ANULACIÓN) ⭐⭐⭐
    // ==========================================

    /**
     * Verificar si el usuario puede anular movimientos
     * (Solo administradores pueden anular)
     *
     * @return bool
     */
    public function puedeAnularMovimientos(): bool
    {
        return $this->esAdmin();
    }

    /**
     * Verificar si el usuario puede restaurar movimientos anulados
     * (Solo administradores pueden restaurar)
     *
     * @return bool
     */
    public function puedeRestaurarMovimientos(): bool
    {
        return $this->esAdmin();
    }

    /**
     * Relación: Movimientos anulados por este usuario
     * (Para auditoría y trazabilidad)
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function movimientosAnulados()
    {
        return $this->hasMany(Movimiento::class, 'anulado_por', 'id');
    }

    /**
     * Obtener estadísticas de acciones del usuario
     * (Movimientos revertidos y anulados)
     *
     * @return array
     */
    public function getEstadisticasAcciones(): array
    {
        return [
            'movimientos_revertidos' => $this->movimientosRevertidos()->count(),
            'movimientos_anulados' => $this->movimientosAnulados()->count(),
            'es_admin' => $this->esAdmin(),
            'puede_revertir' => $this->puedeRevertirBajas(),
            'puede_anular' => $this->puedeAnularMovimientos(),
            'puede_restaurar' => $this->puedeRestaurarMovimientos()
        ];
    }

    /**
     * Verificar si el usuario tiene permisos de auditoría completa
     * (Puede ver movimientos anulados, revertidos, etc.)
     *
     * @return bool
     */
    public function tieneAccesoAuditoria(): bool
    {
        return $this->esAdmin();
    }
}
