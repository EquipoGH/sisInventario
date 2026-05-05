<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('name', 'Guillermo Agip')->first();
$perfilIds = $user->perfiles()->pluck('perfil.idperfil');
$perfilModulos = App\Models\PerfilModulo::query()
    ->whereIn('idperfil', $perfilIds->all())
    ->with([
        'modulo'   => fn ($q) => $q->where('estadomodulo', 'A'),
        'permisos' => fn ($q) => $q->where('estadopermiso', 'A'),
    ])
    ->get()
    ->filter(fn ($pm) => (bool) $pm->modulo)
    ->values();

$menu = $perfilModulos
    ->groupBy('idmodulo')
    ->map(function ($items) {
        $first = $items->first();
        $permisos = $items->flatMap(fn ($pm) => $pm->permisos)
            ->unique('idpermiso')
            ->values();
        $first->setRelation('permisos', $permisos);
        return $first;
    })
    ->values();

foreach($menu as $m) {
    echo $m->modulo->nommodulo . " -> ";
    foreach($m->permisos as $p) {
        echo $p->nombpermiso . ", ";
    }
    echo "\n";
}
