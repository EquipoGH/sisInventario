<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\MovilApiController;

/*
|--------------------------------------------------------------------------
| API Routes — GestInventario
|--------------------------------------------------------------------------
| Rutas para la app móvil Flutter (GestInventario).
| Prefijo automático: /api  (definido en RouteServiceProvider)
| Todas las rutas /api/movil/* no requieren autenticación de sesión web.
|--------------------------------------------------------------------------
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// ============================================================================
// RUTAS MÓVIL — sin autenticación de sesión, solo API token opcional
// ============================================================================
Route::prefix('movil')->group(function () {

    // ── Ping / Health check ─────────────────────────────────────────────────
    Route::get('/ping', [MovilApiController::class, 'ping']);

    // ── Auth ────────────────────────────────────────────────────────────────
    Route::post('/auth/login', [MovilApiController::class, 'login']);

    // ── Catálogos ───────────────────────────────────────────────────────────
    Route::get('/estados-bien',          [MovilApiController::class, 'estadosBien']);
    Route::get('/estados-conservacion',  [MovilApiController::class, 'estadosConservacion']);
    Route::get('/areas',                 [MovilApiController::class, 'areas']);
    Route::get('/ubicaciones',           [MovilApiController::class, 'ubicaciones']);

    // ── Bienes ──────────────────────────────────────────────────────────────
    Route::get('/bienes',                [MovilApiController::class, 'bienes']);
    Route::get('/bien/{codigo}',         [MovilApiController::class, 'detalleBien'])
        ->where('codigo', '.*');

    // ── Inventario ──────────────────────────────────────────────────────────
    // GET  → bien + último movimiento (para módulo Inventario)
    Route::get('/bien/{codigo}/ultimo-movimiento', [MovilApiController::class, 'ultimoMovimientoInventario'])
        ->where('codigo', '.*');

    // PATCH → actualizar SOLO el estado de conservación
    Route::patch('/inventario/{codigo}/conservacion', [MovilApiController::class, 'actualizarConservacion'])
        ->where('codigo', '.*');

    // ── Movimientos ─────────────────────────────────────────────────────────
    Route::post('/movimiento',           [MovilApiController::class, 'registrarMovimiento']);

    // ── Auditoría en lote ───────────────────────────────────────────────────
    Route::post('/bienes/auditoria-lote', [MovilApiController::class, 'auditoriaLote']);
});
