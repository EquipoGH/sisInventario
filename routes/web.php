<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\AreaController;
use App\Http\Controllers\TipoBienController;
use App\Http\Controllers\BienController;
use App\Http\Controllers\EstadoBienController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\TipoMvtoController;
use App\Http\Controllers\DocumentoSustentoController;
use App\Http\Controllers\ResponsableController;
use App\Http\Controllers\UbicacionController;
use App\Http\Controllers\ResponsableAreaController;
use App\Http\Controllers\MovimientoController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\ModuloController;
use App\Http\Controllers\ConfiguracionController;
use App\Http\Controllers\UserPerfilController;
use App\Http\Controllers\PerfilModuloController;
use App\Http\Controllers\PerfilModuloPermisoController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

// Página de bienvenida (pública)
Route::get('/', function () {
    return view('welcome');
});

// Rutas protegidas con autenticación
Route::middleware(['auth', 'verified'])->group(function () {

    // ==================== DASHBOARD ====================
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // ==================== CONFIGURACIÓN ====================
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])
        ->name('configuracion.index');
    Route::post('/configuracion', [ConfiguracionController::class, 'actualizar'])
        ->name('configuracion.actualizar');

    // ==================== DOCUMENTO SUSTENTO ====================
    // ⚠️ IMPORTANTE: Rutas específicas ANTES del resource

    Route::post('/documento-sustento/verificar-numero', [DocumentoSustentoController::class, 'verificarNumero'])
        ->name('documento-sustento.verificar-numero');

    Route::get('/documento-sustento/obtener', [DocumentoSustentoController::class, 'obtenerDocumentos'])
        ->name('documento-sustento.obtener-documentos');

    Route::get('/documento-sustento/{documento_sustento}/bienes', [DocumentoSustentoController::class, 'bienes'])
        ->name('documento-sustento.bienes');

    Route::post('/documento-sustento/{documento_sustento}/desvincular-bienes', [DocumentoSustentoController::class, 'desvincularBienes'])
        ->name('documento-sustento.desvincular-bienes');

    // Resource CRUD
    Route::resource('documento-sustento', DocumentoSustentoController::class);

    // ==================== BIEN ====================
    // ⚠️ IMPORTANTE: Rutas específicas ANTES del resource

    Route::post('/bien/verificar-codigo', [BienController::class, 'verificarCodigo'])
        ->name('bien.verificar-codigo');

    Route::get('/bien/obtener-documentos', [BienController::class, 'obtenerDocumentos'])
        ->name('bien.obtener-documentos');

    // Resource CRUD
    Route::resource('bien', BienController::class);

    // ==================== GESTIÓN DE INVENTARIO ====================
    Route::resource('area', AreaController::class);
    Route::resource('tipo-bien', TipoBienController::class);
    Route::resource('estado-bien', EstadoBienController::class);
    Route::resource('tipo-mvto', TipoMvtoController::class);
    Route::resource('responsable', ResponsableController::class);
    Route::resource('ubicacion', UbicacionController::class);
    Route::resource('responsable-area', ResponsableAreaController::class);

    // ==================== MOVIMIENTO ====================
    // ⚠️ IMPORTANTE: Rutas específicas ANTES del resource

    // Asignación masiva de movimientos
    Route::post('movimiento/asignar-masivo', [MovimientoController::class, 'asignarMasivo'])
        ->name('movimiento.asignar-masivo');

    // Crear movimientos masivos
    Route::post('movimiento/crear-masivo', [MovimientoController::class, 'crearMasivo'])
        ->name('movimiento.crear-masivo');

    // Eliminar movimientos masivos
    Route::post('movimiento/eliminar-masivo', [MovimientoController::class, 'eliminarMasivo'])
        ->name('movimiento.eliminar-masivo');

    // Filtros adicionales
    Route::get('movimiento/por-tipo', [MovimientoController::class, 'porTipo'])
        ->name('movimiento.por-tipo');

    Route::get('movimiento/por-bien', [MovimientoController::class, 'porBien'])
        ->name('movimiento.por-bien');

    Route::get('movimiento/por-fecha', [MovimientoController::class, 'porFecha'])
        ->name('movimiento.por-fecha');

    Route::get('movimiento/estadisticas', [MovimientoController::class, 'estadisticas'])
        ->name('movimiento.estadisticas');

    // Resource CRUD (SIEMPRE AL FINAL)
    Route::resource('movimiento', MovimientoController::class);

    // ==================== SEGURIDAD ====================

    // Módulo
    Route::delete('user/bulk-destroy', [UserController::class, 'bulkDestroy'])
        ->name('user.bulk-destroy');
    Route::resource('user', UserController::class)->except(['show', 'create']);

    // Perfil
    Route::delete('perfil/bulk-destroy', [PerfilController::class, 'bulkDestroy'])
        ->name('perfil.bulk-destroy');
    Route::resource('perfil', PerfilController::class)->except(['show', 'create']);

    // Permiso
    Route::delete('permiso/bulk-destroy', [PermisoController::class, 'bulkDestroy'])
        ->name('permiso.bulk-destroy');
    Route::resource('permiso', PermisoController::class)->except(['show', 'create']);

    // Módulo
    Route::delete('modulo/bulk-destroy', [ModuloController::class, 'bulkDestroy'])
        ->name('modulo.bulk-destroy');
    Route::resource('modulo', ModuloController::class)->except(['show', 'create']);

    // Usuario Perfil
    Route::get('/user/{user}/perfiles', [UserPerfilController::class, 'edit'])->name('users.perfiles.edit');
    Route::put('/user/{user}/perfiles', [UserPerfilController::class, 'update'])->name('users.perfiles.update');

    // Perfil Modulo
    Route::get('/perfil/{perfil}/modulos', [PerfilModuloController::class, 'edit'])
        ->name('perfil.modulos.edit');
    Route::put('/perfil/{perfil}/modulos', [PerfilModuloController::class, 'update'])
        ->name('perfil.modulos.update');

    // Modulo Permisos
    Route::get('/perfil-modulo/{perfilModulo}/permisos', [PerfilModuloPermisoController::class, 'edit'])
        ->name('perfil-modulo.permisos.edit');
    Route::put('/perfil-modulo/{perfilModulo}/permisos', [PerfilModuloPermisoController::class, 'update'])
        ->name('perfil-modulo.permisos.update');
    Route::get('/perfil-modulo/{perfilModulo}/permisos/json', [PerfilModuloPermisoController::class, 'index'])
        ->name('perfil-modulo.permisos.index');

});

require __DIR__.'/auth.php';
