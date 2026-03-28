<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

DB::statement("SELECT setval('area_id_area_seq', (SELECT MAX(id_area) FROM area))");
echo "Sequence area_id_area_seq updated.\n";
