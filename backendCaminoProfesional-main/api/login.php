<?php
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

session_start();

require_once __DIR__ . '/../models/AccesoDatos.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

$data = json_decode(file_get_contents("php://input"), true);

$email = trim($data['email'] ?? '');
$clave = trim($data['clave'] ?? '');

if (empty($email) || empty($clave)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Email y contraseña obligatorios']);
    exit();
}

try {
    // Caso especial: admin hardcoded
    if ($email === 'admin' && $clave === 'admin') {
        $_SESSION['usuario'] = [
            'tipo' => 'ADMIN',
            'nombre' => 'Administrador',
            'email' => 'admin'
        ];
        $_SESSION['ultimo_acceso'] = time();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'nombre' => 'Administrador',
                'email' => 'admin',
                'esAdmin' => true
            ]
        ]);
        exit();
    }
    
    // Usuario normal
    $db = AccesoDatos::getModelo();
    $usuario = $db->getUsuarioPorEmail($email);
    
    if ($usuario && password_verify($clave, $usuario->CLAVE)) {
        $_SESSION['usuario'] = [
            'tipo' => 'USUARIO',
            'nombre' => $usuario->NOMBRE,
            'email' => $usuario->EMAIL,
            'apellido' => $usuario->APELLIDO ?? '',
            'grados' => $usuario->GRADOS ?? ''
        ];
        $_SESSION['ultimo_acceso'] = time();
        
        http_response_code(200);
        echo json_encode([
            'success' => true,
            'message' => 'Login exitoso',
            'user' => [
                'nombre' => $usuario->NOMBRE,
                'email' => $usuario->EMAIL,
                'apellido' => $usuario->APELLIDO ?? '',
                'grados' => $usuario->GRADOS ?? '',
                'esAdmin' => false
            ]
        ]);
    } else {
        http_response_code(401);
        echo json_encode([
            'success' => false,
            'message' => 'Credenciales incorrectas'
        ]);
    }
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error del servidor: ' . $e->getMessage()
    ]);
}