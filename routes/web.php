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
use App\Http\Controllers\PerfilController;
use App\Http\Controllers\PermisoController;
use App\Http\Controllers\ModuloController;




use Illuminate\Support\Facades\Route;



/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| Here is where you can register web routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "web" middleware group. Make something great!
|
*/

// Página de bienvenida (pública)
Route::get('/', function () {
    return view('welcome');
});

// Rutas protegidas con autenticación
Route::middleware(['auth', 'verified'])->group(function () {

    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');


    // GESTIÓN DE INVENTARIO
    Route::resource('area', AreaController::class);
    Route::resource('tipo-bien', TipoBienController::class);
    Route::resource('bien', BienController::class);
    Route::post('/bien/verificar-codigo', [App\Http\Controllers\BienController::class, 'verificarCodigo'])->name('bien.verificar-codigo');
    //RUTAS DE LOS COLORES
    // Rutas de Configuración del Sistema
Route::get('/configuracion', [App\Http\Controllers\ConfiguracionController::class, 'index'])
    ->name('configuracion.index');
Route::post('/configuracion', [App\Http\Controllers\ConfiguracionController::class, 'actualizar'])
    ->name('configuracion.actualizar');

    Route::resource('estado-bien', EstadoBienController::class);
    Route::resource('tipo-mvto', TipoMvtoController::class);
    Route::resource('documento-sustento', DocumentoSustentoController::class);
    Route::resource('responsable', ResponsableController::class);
    Route::resource('ubicacion', UbicacionController::class);
    Route::resource('responsable-area', ResponsableAreaController::class);
    Route::resource('movimiento', MovimientoController::class);

    // Seguridad
        Route::delete('perfil/bulk-destroy', [PerfilController::class, 'bulkDestroy'])
            ->name('perfil.bulk-destroy');
        Route::resource('perfil', PerfilController::class)->except(['show', 'create']);

        Route::delete('permiso/bulk-destroy', [PermisoController::class, 'bulkDestroy'])
            ->name('permiso.bulk-destroy');
        Route::resource('permiso', PermisoController::class)->except(['show', 'create']);

        Route::delete('modulo/bulk-destroy', [ModuloController::class, 'bulkDestroy'])
            ->name('modulo.bulk-destroy');
        Route::resource('modulo', ModuloController::class)->except(['show', 'create']);




});

require __DIR__.'/auth.php';



