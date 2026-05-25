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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Método no permitido']);
    exit();
}

// 🛡️ COMPROBACIÓN DE SESIÓN ORIGINAL
if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Debes iniciar sesión para comentar']);
    exit();
}

require_once __DIR__ . '/../models/AccesoDatos.php';

$data = json_decode(file_get_contents("php://input"), true);
$textoComentario = trim($data['comentario'] ?? '');

if (empty($textoComentario)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El comentario no puede estar vacío']);
    exit();
}

// 💡 AQUÍ ESTABA EL DETALLE:
// Extraemos el usuario de la sesión. 
$usuario = $_SESSION['usuario'];

// Tu login guarda al admin de forma distinta a un usuario normal. 
// Con esta línea nos aseguramos de sacar el nombre sea cual sea el formato:
if (is_object($usuario)) {
    $nombreAutor = $usuario->NOMBRE ?? $usuario->nombre ?? 'Administrador';
} else if (is_array($usuario)) {
    $nombreAutor = $usuario['NOMBRE'] ?? $usuario['nombre'] ?? 'Administrador';
} else {
    $nombreAutor = (string)$usuario; // Por si guardaste solo el texto "admin"
}

try {
    $db = AccesoDatos::getModelo();
    $guardado = $db->insertarComentario($nombreAutor, $textoComentario);
    
    if ($guardado) {
        http_response_code(201);
        echo json_encode(['success' => true, 'message' => 'Comentario publicado']);
    } else {
        throw new Exception('No se pudo guardar en la base de datos');
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error del servidor: ' . $e->getMessage()]);
}
?>