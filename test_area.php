<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $area = \App\Models\Area::create(['nombre_area' => 'PRUEBA_TINKER']);
    echo "Success: " . $area->id_area;
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
