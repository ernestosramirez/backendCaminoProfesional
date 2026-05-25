<?php
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/AccesoDatos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$email    = trim($data['email'] ?? '');
$nombre   = trim($data['nombre'] ?? '');
$apellido = trim($data['apellido'] ?? '');
$telefono = trim($data['telefono'] ?? '');
$grados   = trim($data['grados'] ?? '');

if (empty($email) || empty($nombre) || empty($apellido)) {
    http_response_code(400);
    echo json_encode([
        'success' => false, 
        'message' => 'Email, nombre y apellido son obligatorios'
    ]);
    exit();
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email inválido']);
    exit();
}

try {
    $db = AccesoDatos::getModelo();
    
    $usuarioExistente = $db->getUsuarioPorEmail($email);
    if ($usuarioExistente) {
        http_response_code(409);
        echo json_encode(['success' => false, 'message' => 'El email ya está registrado']);
        exit();
    }
    
    $resultado = $db->crearUsuario($email, $nombre, $apellido, $telefono, $grados, '1234');
    
    if ($resultado) {
        http_response_code(201);
        echo json_encode([
            'success' => true,
            'message' => 'Usuario registrado correctamente'
        ]);
    } else {
        throw new Exception('Error al insertar en la base de datos');
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}
?>