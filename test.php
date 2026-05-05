<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('name', 'Guillermo Agip')->first() ?? App\Models\User::first();
echo "User: " . $user->name . " (Rol: " . $user->rol_usuario . ")\n";
$pms = App\Models\PerfilModulo::whereIn('idperfil', $user->perfiles->pluck('idperfil'))->with('permisos')->get();
foreach($pms as $pm) {
    echo $pm->modulo->nommodulo . " -> ";
    foreach($pm->permisos as $p) {
        echo $p->nombpermiso . ", ";
    }
    echo "\n";
}
