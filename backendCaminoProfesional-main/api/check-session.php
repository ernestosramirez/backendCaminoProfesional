<?php
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

$inactividad = 600;
if (isset($_SESSION['usuario']) && isset($_SESSION['ultimo_acceso'])) {
    $vida_session = time() - $_SESSION['ultimo_acceso'];
    
    if ($vida_session > $inactividad) {
        session_unset();
        session_destroy();
        http_response_code(401);
        echo json_encode(['authenticated' => false, 'message' => 'Sesión expirada']);
        exit();
    }
    
    $_SESSION['ultimo_acceso'] = time();
}

if (isset($_SESSION['usuario'])) {
    $user = $_SESSION['usuario'];
    
    http_response_code(200);
    echo json_encode([
        'authenticated' => true,
        'user' => [
            'nombre' => $user['nombre'],
            'email' => $user['email'],
            'esAdmin' => ($user['tipo'] === 'ADMIN')
        ]
    ]);
} else {
    http_response_code(401);
    echo json_encode(['authenticated' => false]);
}
?>