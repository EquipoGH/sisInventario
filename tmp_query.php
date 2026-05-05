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
    echo json_encode($bien, JSON_PRETTY_PRINT);
}
