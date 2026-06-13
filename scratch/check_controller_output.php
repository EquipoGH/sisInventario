<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$app->make('auth')->loginUsingId(1);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::create(
        '/inventario/7/bienes-disponibles',
        'GET',
        ['q' => 'CPU', 'incidencia' => 1]
    )
);
echo $response->getContent();
