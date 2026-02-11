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
use App\Http\Controllers\ReporteBienController;
use App\Http\Controllers\SystemSettingController;
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

    // ⭐ NUEVA: API para actualización en tiempo real (AJAX)
    Route::get('/dashboard/stats', [DashboardController::class, 'getStats'])->name('dashboard.stats');

    // ==================== PROFILE (Breeze) ====================
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // ==================== CONFIGURACIÓN ====================
    // Apariencia
    Route::get('/configuracion', [ConfiguracionController::class, 'index'])->name('configuracion.index');
    Route::post('/configuracion', [ConfiguracionController::class, 'actualizar'])->name('configuracion.actualizar');

    // Institución (System Settings)
    Route::get('/configuracion/institucion', [SystemSettingController::class, 'edit'])->name('configuracion.institucion');
    Route::put('/configuracion/institucion', [SystemSettingController::class, 'update'])->name('configuracion.institucion.update');

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

    // ⭐⭐⭐ NUEVA: Obtener último movimiento antes de eliminar ⭐⭐⭐
    Route::get('/bien/{bien}/ultimo-movimiento', [BienController::class, 'obtenerUltimoMovimiento'])
        ->name('bien.ultimo-movimiento');

    // ✅ ELIMINACIÓN LÓGICA
    Route::get('/bien/eliminados', [BienController::class, 'eliminados'])
        ->name('bien.eliminados');

    Route::post('/bien/restaurar/{id}', [BienController::class, 'restaurar'])
        ->name('bien.restaurar');

    // ⭐ RESOURCE AL FINAL (para no sobrescribir rutas personalizadas)
    Route::resource('bien', BienController::class);

    // ==================== GESTIÓN DE INVENTARIO ====================
    Route::resource('area', AreaController::class);
    Route::resource('tipo-bien', TipoBienController::class);
    Route::resource('estado-bien', EstadoBienController::class);
    Route::resource('tipo-mvto', TipoMvtoController::class);
    Route::resource('responsable', ResponsableController::class);

    // ✅ UBICACIÓN CON RUTAS DE RECEPCIÓN
    Route::post('ubicacion/{ubicacion}/marcar-recepcion', [UbicacionController::class, 'marcarRecepcion'])
        ->name('ubicacion.marcar-recepcion');
    Route::post('ubicacion/{ubicacion}/desmarcar-recepcion', [UbicacionController::class, 'desmarcarRecepcion'])
        ->name('ubicacion.desmarcar-recepcion');
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

    // ⭐ PDF DE TRAZABILIDAD
    Route::get('movimiento/pdf-trazabilidad/{bien}', [MovimientoController::class, 'generarPDFTrazabilidad'])
        ->name('movimiento.pdf.trazabilidad');

    // ⭐ FILTROS
    Route::get('movimiento/por-tipo', [MovimientoController::class, 'porTipo'])
        ->name('movimiento.por-tipo');

    Route::get('movimiento/por-bien', [MovimientoController::class, 'porBien'])
        ->name('movimiento.por-bien');

    Route::get('movimiento/por-fecha', [MovimientoController::class, 'porFecha'])
        ->name('movimiento.por-fecha');

    // ⭐ OPERACIONES MASIVAS
    Route::post('movimiento/asignar-masivo', [MovimientoController::class, 'asignarMasivo'])
        ->name('movimiento.asignar-masivo');

    Route::post('movimiento/baja-masivo', [MovimientoController::class, 'bajarMasivo'])
        ->name('movimiento.baja-masivo');

    Route::post('movimiento/crear-masivo', [MovimientoController::class, 'crearMasivo'])
        ->name('movimiento.crear-masivo');

    // ⭐⭐⭐ CAMBIO: ELIMINAR FÍSICO → ANULAR (SOFT DELETE) ⭐⭐⭐
    Route::post('movimiento/anular-masivo', [MovimientoController::class, 'anularMasivo'])
        ->name('movimiento.anular-masivo');

    // ⭐ REVERTIR BAJA INDIVIDUAL (SOLO ADMIN)
    Route::post('movimiento/revertir-baja/{bien}', [MovimientoController::class, 'revertirBaja'])
        ->name('movimiento.revertir-baja');

    // ⭐⭐⭐ NUEVAS RUTAS: ANULAR Y RESTAURAR MOVIMIENTOS ⭐⭐⭐
    Route::post('movimiento/{movimiento}/anular', [MovimientoController::class, 'anular'])
        ->name('movimiento.anular');

    Route::post('movimiento/{movimiento}/restaurar', [MovimientoController::class, 'restaurar'])
        ->name('movimiento.restaurar');

    // ⭐ BIENES ELIMINADOS (Compatibilidad - redirige a BienController)
    Route::get('movimiento/bienes-eliminados', [BienController::class, 'eliminados'])
        ->name('movimiento.bienes-eliminados');

    Route::post('movimiento/restaurar-bien/{bien}', [BienController::class, 'restaurar'])
        ->name('movimiento.restaurar-bien');

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
        // Reporte Kardex
        Route::get('/kardex', [ReporteKardexController::class, 'index'])->name('kardex.index');
        Route::get('/kardex/data', [ReporteKardexController::class, 'data'])->name('kardex.data');
        Route::get('/kardex/pdf', [ReporteKardexController::class, 'pdf'])->name('kardex.pdf');
        Route::get('/kardex/excel', [ReporteKardexController::class, 'excel'])->name('kardex.excel');

        // Reporte de Bienes
        Route::get('/bienes', [ReporteBienController::class, 'index'])->name('bienes.index');
        Route::get('/bienes/data', [ReporteBienController::class, 'data'])->name('bienes.data');
        Route::get('/bienes/pdf', [ReporteBienController::class, 'pdf'])->name('bienes.pdf');
        Route::get('/bienes/excel', [ReporteBienController::class, 'excel'])->name('bienes.excel');
    });
});

require __DIR__ . '/auth.php';
