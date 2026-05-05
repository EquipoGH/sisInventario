<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);

$host = '127.0.0.1';
$port = 5432;
$db = 'AdminLTE';
$user = 'postgres';
$pass = '12345678';

try {
    $dsn = "pgsql:host=$host;port=$port;dbname=$db";
    $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
} catch (Exception $e) {
    echo "DB_CONN_ERROR: " . $e->getMessage() . PHP_EOL;
    exit(2);
}

try {
    $q1 = "SELECT id, name, rol_usuario, id_responsable FROM users WHERE rol_usuario = 'INFORMATICA' ORDER BY id";
    $sth = $pdo->query($q1);
    $users = $sth->fetchAll(PDO::FETCH_ASSOC);

    $result = [];
    foreach ($users as $u) {
        $dni = $u['id_responsable'];
        $areas = [];
        if ($dni) {
            $sth2 = $pdo->prepare("SELECT idarea FROM responsable_area WHERE dni_responsable = ?");
            $sth2->execute([$dni]);
            $areasRows = $sth2->fetchAll(PDO::FETCH_ASSOC);
            foreach ($areasRows as $ar) {
                $areas[] = $ar['idarea'];
            }
        }

        $arr = [
            'esAdmin' => strtoupper($u['rol_usuario']) === 'ADMIN',
            'rol' => $u['rol_usuario'],
            'id_responsable' => $u['id_responsable'],
            'idsAreas' => $areas
        ];

        $result[] = ['user' => $u, 'dd' => $arr];
    }

    echo json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . PHP_EOL;
    exit(3);
}
