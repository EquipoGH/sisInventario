<?php

namespace App\Helpers;

use Illuminate\Support\Facades\Auth;
use App\Models\Bien;
use App\Models\Area;

class PermisosHelper
{
    /**
     * ⭐ Verificar si es ADMIN
     */
    public static function esAdmin(): bool
    {
        return Auth::check() && strtoupper(Auth::user()->rol_usuario) === 'ADMIN';
    }

    /**
     * ⭐ Verificar si es INFORMÁTICA
     */
    public static function esInformatica(): bool
    {
        return Auth::check() && strtoupper(Auth::user()->rol_usuario) === 'INFORMATICA';
    }

    /**
     * ⭐ Verificar si es INVITADO
     */
    public static function esInvitado(): bool
    {
        return Auth::check() && strtoupper(Auth::user()->rol_usuario) === 'INVITADO';
    }

    /**
     * ⭐⭐⭐ FILTRAR BIENES POR ÁREAS DEL USUARIO (CORREGIDO) ⭐⭐⭐
     */
    public static function getBienesQuery()
    {
        $user = Auth::user();

        // ⭐⭐⭐ SOLO ADMIN ve TODOS los bienes (sin restricción de área) ⭐⭐⭐
        if (self::esAdmin()) {
            return Bien::query();
        }

        // ⭐⭐⭐ TODOS LOS DEMÁS (INFORMATICA, USUARIO, etc.) filtran por área ⭐⭐⭐
        $areasPermitidas = $user->getIdsAreasPermitidas();

        // Si no tiene áreas asignadas, no ve nada
        if (empty($areasPermitidas)) {
            return Bien::whereRaw('1 = 0'); // Query vacía
        }

        // ⭐⭐⭐ USUARIOS: Solo ven bienes con último movimiento en sus áreas ⭐⭐⭐
        return Bien::whereHas('latestMovimiento', function($q) use ($areasPermitidas) {
            $q->where('anulado', false)
              ->whereHas('ubicacion', function($qq) use ($areasPermitidas) {
                  $qq->whereIn('idarea', $areasPermitidas);
              });
        });
    }

    /**
     * ⭐ Verificar si puede ver un bien específico
     */
    public static function puedeVerBien($bien): bool
    {
        $user = Auth::user();

        // ADMIN ve todo
        if (self::esAdmin()) {
            return true;
        }

        // ⭐⭐⭐ INFORMATICA también filtra por área (YA NO VE TODO) ⭐⭐⭐
        // Obtener último movimiento del bien
        $ultimoMov = $bien->movimientos()
            ->with('ubicacion')
            ->where('anulado', false)
            ->orderBy('fecha_mvto', 'desc')
            ->first();

        // ⭐⭐⭐ Si no tiene movimientos, SOLO ADMIN puede verlo ⭐⭐⭐
        if (!$ultimoMov) {
            return false;
        }

        // Verificar si la ubicación del último movimiento pertenece a sus áreas
        $areaDelBien = $ultimoMov->ubicacion->idarea ?? null;
        return $areaDelBien && $user->tieneAccesoArea($areaDelBien);
    }

    /**
     * ⭐ Verificar si puede editar un bien
     */
    public static function puedeEditarBien($bien): bool
    {
        $user = Auth::user();

        // ADMIN puede editar todo
        if (self::esAdmin()) {
            return true;
        }

        // ⭐⭐⭐ INFORMÁTICA solo puede editar bienes de su área ⭐⭐⭐
        if (self::esInformatica()) {
            return self::puedeVerBien($bien); // Solo si pertenece a su área
        }

        return false;
    }

    /**
     * ⭐ Verificar si puede registrar bienes
     */
    public static function puedeRegistrarBien(): bool
    {
        return self::esAdmin() || self::esInformatica();
    }

    /**
     * ⭐ Verificar si puede eliminar bienes (SOLO ADMIN)
     */
    public static function puedeEliminarBien(): bool
    {
        return self::esAdmin();
    }

    /**
     * ⭐ Obtener ubicaciones permitidas según áreas del usuario
     */
    public static function getUbicacionesPermitidas()
    {
        $user = Auth::user();

        // ADMIN ve todas las ubicaciones
        if (self::esAdmin()) {
            return \App\Models\Ubicacion::all();
        }

        // ⭐⭐⭐ TODOS LOS DEMÁS (incluyendo INFORMATICA) filtran por área ⭐⭐⭐
        $areasPermitidas = $user->getIdsAreasPermitidas();

        return \App\Models\Ubicacion::whereIn('idarea', $areasPermitidas)->get();
    }

    /**
     * ⭐⭐⭐ FILTRAR MOVIMIENTOS POR ÁREAS DEL USUARIO (OPTIMIZADO) ⭐⭐⭐
     */
    public static function getMovimientosQuery()
    {
        $user = Auth::user();

        // ⭐ SOLO ADMIN ve TODOS los movimientos
        if (self::esAdmin()) {
            return \App\Models\Movimiento::query();
        }

        // ⭐ TODOS LOS DEMÁS (INFORMATICA, USUARIO, etc.) filtran por área
        $areasPermitidas = $user->getIdsAreasPermitidas();

        if (empty($areasPermitidas)) {
            return \App\Models\Movimiento::whereRaw('1 = 0'); // Query vacía
        }

        // ⭐⭐⭐ LÓGICA OPTIMIZADA ⭐⭐⭐
        return \App\Models\Movimiento::where(function($query) use ($areasPermitidas, $user) {

            // 1️⃣ Movimientos con ubicación en áreas permitidas (DE CUALQUIER USUARIO)
            $query->whereHas('ubicacion', function($q) use ($areasPermitidas) {
                $q->whereIn('idarea', $areasPermitidas);
            });

            // 2️⃣ O movimientos SIN ASIGNAR creados por el usuario actual (solo INFORMATICA)
            if (self::esInformatica()) {
                $query->orWhere(function($q) use ($user) {
                    $q->where('idusuario', $user->id) // ⭐ CLAVE: Solo los que creó el usuario
                      ->where(function($qq) {
                          // Sin ubicación O tipo "SIN ASIGNAR" o "REGISTRO"
                          $qq->whereNull('idubicacion')
                             ->orWhereHas('tipoMovimiento', function($qqq) {
                                 $qqq->where('tipo_mvto', 'ILIKE', '%sin asignar%')
                                     ->orWhere('tipo_mvto', 'ILIKE', '%registro%');
                             });
                      });
                });
            }
        });
    }
}
