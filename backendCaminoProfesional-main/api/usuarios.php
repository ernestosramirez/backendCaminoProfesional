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

require_once __DIR__ . '/../models/AccesoDatos.php';

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipo'] !== 'ADMIN') {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

try {
    $db = AccesoDatos::getModelo();
    $usuarios = $db->getTodosLosUsuarios();
    
    $usuariosTransformados = array_map(function($u) {
        return [
            'email' => $u->EMAIL,
            'nombre' => $u->NOMBRE,
            'apellido' => $u->APELLIDO ?? '',
            'telefono' => $u->TELEFONO ?? '',
            'grados' => $u->GRADOS ?? '',
            'esAdmin' => false
        ];
    }, $usuarios);
    
    http_response_code(200);
    echo json_encode([
        'success' => true,
        'data' => $usuariosTransformados
    ]);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>