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
use App\Http\Controllers\ReporteKardexController;
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

    // ==================== PROFILE (Breeze) ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== CONFIGURACIÓN ====================
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion', [ConfiguracionController::class, 'actualizar'])->name('configuracion.actualizar');

    // ==================== DOCUMENTO SUSTENTO ====================
    Route::post('/documento-sustento/verificar-numero', [DocumentoSustentoController::class, 'verificarNumero'])
        ->name('documento-sustento.verificar-numero');

    Route::get('/documento-sustento/obtener', [DocumentoSustentoController::class, 'obtenerDocumentos'])
        ->name('documento-sustento.obtener-documentos');

    Route::get('/documento-sustento/{documento_sustento}/bienes', [DocumentoSustentoController::class, 'bienes'])
        ->name('documento-sustento.bienes');

    Route::post('/documento-sustento/{documento_sustento}/desvincular-bienes', [DocumentoSustentoController::class, 'desvincularBienes'])
        ->name('documento-sustento.desvincular-bienes');

    Route::resource('documento-sustento', DocumentoSustentoController::class);

    // ==================== BIEN ====================
    Route::post('/bien/verificar-codigo', [BienController::class, 'verificarCodigo'])
        ->name('bien.verificar-codigo');

    Route::get('/bien/obtener-documentos', [BienController::class, 'obtenerDocumentos'])
        ->name('bien.obtener-documentos');

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
    // ⚠️ IMPORTANTE: Las rutas personalizadas DEBEN ir ANTES del Route::resource

    // ⭐ ESTADÍSTICAS AJAX (PARA ACTUALIZAR CARDS EN TIEMPO REAL)
    Route::get('movimiento/estadisticas', [MovimientoController::class, 'getEstadisticas'])
        ->name('movimiento.estadisticas');

    // ⭐ TRAZABILIDAD DE BIEN
    Route::get('movimiento/trazabilidad/{bien}', [MovimientoController::class, 'trazabilidad'])
        ->name('movimiento.trazabilidad');

    // ⭐ FILTROS
    Route::get('movimiento/por-tipo', [MovimientoController::class, 'porTipo'])
        ->name('movimiento.por-tipo');

    Route::get('movimiento/por-bien', [MovimientoController::class, 'porBien'])
        ->name('movimiento.por-bien');

    Route::get('movimiento/por-fecha', [MovimientoController::class, 'porFecha'])
        ->name('movimiento.por-fecha');

    // ⭐ ASIGNACIÓN MASIVA (tipo forzado a ASIGNACIÓN)
    Route::post('movimiento/asignar-masivo', [MovimientoController::class, 'asignarMasivo'])
        ->name('movimiento.asignar-masivo');

    // ⭐ BAJA MASIVA (tipo forzado a BAJA)
    Route::post('movimiento/baja-masivo', [MovimientoController::class, 'bajarMasivo'])
        ->name('movimiento.baja-masivo');

    // ⭐ REVERTIR BAJA INDIVIDUAL (SOLO ADMIN)
    Route::post('movimiento/revertir-baja/{bien}', [MovimientoController::class, 'revertirBaja'])
        ->name('movimiento.revertir-baja');

    // ⭐ CREAR MOVIMIENTOS MASIVOS
    Route::post('movimiento/crear-masivo', [MovimientoController::class, 'crearMasivo'])
        ->name('movimiento.crear-masivo');

    // ⭐ ELIMINAR MOVIMIENTOS MASIVOS
    Route::post('movimiento/eliminar-masivo', [MovimientoController::class, 'eliminarMasivo'])
        ->name('movimiento.eliminar-masivo');

    // ⭐ Generar PDF de trazabilidad
    Route::get('/movimiento/pdf-trazabilidad/{bien}', [MovimientoController::class, 'generarPDFTrazabilidad'])
    ->name('movimiento.pdf.trazabilidad');



    // ⭐ RESOURCE AL FINAL (para que no sobreescriba las rutas personalizadas)
    Route::resource('movimiento', MovimientoController::class);

    // ==================== SEGURIDAD ====================
    Route::delete('user/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('user.bulk-destroy');
    Route::resource('user', UserController::class)->except(['show', 'create']);

    Route::get('/user/{user}/perfiles', [UserPerfilController::class, 'edit'])->name('users.perfiles.edit');
    Route::put('/user/{user}/perfiles', [UserPerfilController::class, 'update'])->name('users.perfiles.update');

    Route::delete('perfil/bulk-destroy', [PerfilController::class, 'bulkDestroy'])->name('perfil.bulk-destroy');
    Route::resource('perfil', PerfilController::class)->except(['show', 'create']);

    Route::get('/perfil/{perfil}/modulos', [PerfilModuloController::class, 'edit'])->name('perfil.modulos.edit');
    Route::put('/perfil/{perfil}/modulos', [PerfilModuloController::class, 'update'])->name('perfil.modulos.update');

    Route::delete('permiso/bulk-destroy', [PermisoController::class, 'bulkDestroy'])->name('permiso.bulk-destroy');
    Route::resource('permiso', PermisoController::class)->except(['show', 'create']);

    Route::get('/perfil-modulo/{perfilModulo}/permisos', [PerfilModuloPermisoController::class, 'edit'])
        ->name('perfil-modulo.permisos.edit');
    Route::put('/perfil-modulo/{perfilModulo}/permisos', [PerfilModuloPermisoController::class, 'update'])
        ->name('perfil-modulo.permisos.update');
    Route::get('/perfil-modulo/{perfilModulo}/permisos/json', [PerfilModuloPermisoController::class, 'index'])
        ->name('perfil-modulo.permisos.index');

    Route::delete('modulo/bulk-destroy', [ModuloController::class, 'bulkDestroy'])->name('modulo.bulk-destroy');
    Route::resource('modulo', ModuloController::class)->except(['show', 'create']);

    // ==================== REPORTES ====================
    Route::prefix('reportes')->name('reportes.')->group(function () {
        Route::get('/kardex', [ReporteKardexController::class, 'index'])->name('kardex.index');
        Route::get('/kardex/data', [ReporteKardexController::class, 'data'])->name('kardex.data');
        Route::get('/kardex/pdf', [ReporteKardexController::class, 'pdf'])->name('kardex.pdf');
        Route::get('/kardex/excel', [ReporteKardexController::class, 'excel'])->name('kardex.excel');
    });
});

require __DIR__ . '/auth.php';
