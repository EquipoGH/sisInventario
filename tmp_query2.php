<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$codigo = '42202400001';
$bien = App\Models\Bien::with(['movimientos' => function($q) {
    $q->orderBy('fecha_mvto', 'desc');
}, 'movimientos.tipoMovimiento', 'movimientos.ubicacion.area', 'tipoBien'])->where('codigo_patrimonial', $codigo)->first();

if (!$bien) {
    echo "NO FOUND\n";
} else {
    echo "=== TIPO BIEN: " . ($bien->tipoBien ? $bien->tipoBien->nombre_tipo : 'N/A') . " ===\n";
    echo "=== DENOMINACION: " . $bien->denominacion_bien . " ===\n";
    echo "=== MOVIMIENTOS ===\n";
    foreach ($bien->movimientos as $m) {
        $tipo = $m->tipoMovimiento ? $m->tipoMovimiento->tipo_mvto : 'N/A';
        $area = $m->ubicacion && $m->ubicacion->area ? $m->ubicacion->area->nombre_area : 'N/A';
        $anulado = $m->anulado ? " [ANULADO]" : "";
        echo "ID: $m->id_movimiento | Fecha: $m->fecha_mvto | Tipo: $tipo | Area: $area | Resp: $m->idusuario $anulado\n";
    }
}
