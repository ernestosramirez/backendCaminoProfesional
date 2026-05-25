<?php
header('Access-Control-Allow-Origin: http://localhost:4200');
header('Access-Control-Allow-Methods: GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type, X-Requested-With');
header('Access-Control-Allow-Credentials: true');
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit();
}

require_once __DIR__ . '/../models/AccesoDatos.php';

try {
    $db = AccesoDatos::getModelo();
    $comentarios = $db->getComentarios();
    
    http_response_code(200);
    echo json_encode(['success' => true, 'data' => $comentarios]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al obtener comentarios: ' . $e->getMessage()]);
}
?>