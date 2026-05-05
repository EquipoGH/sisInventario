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
    $q1 = "SELECT id, name, rol_usuario, id_responsable FROM users WHERE rol_usuario = 'INFORMATICA'";
    $sth = $pdo->query($q1);
    $rows1 = $sth->fetchAll(PDO::FETCH_ASSOC);

    echo "--- QUERY 1 RESULT ---\n";
    echo json_encode($rows1, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL . PHP_EOL;

    $q2 = "SELECT ra.* FROM responsable_area ra WHERE ra.dni_responsable = (
      SELECT id_responsable FROM users WHERE rol_usuario = 'INFORMATICA' LIMIT 1
    )";

    $sth2 = $pdo->query($q2);
    $rows2 = $sth2->fetchAll(PDO::FETCH_ASSOC);

    echo "--- QUERY 2 RESULT ---\n";
    echo json_encode($rows2, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . PHP_EOL;

} catch (Exception $e) {
    echo "QUERY_ERROR: " . $e->getMessage() . PHP_EOL;
    exit(3);
}
