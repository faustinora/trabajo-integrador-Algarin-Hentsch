<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$datos = json_decode(file_get_contents('php://input'), true);
$id_usuario = (int)($datos['id_usuario'] ?? 0);

if ($id_usuario <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de usuario no válido.']);
    exit();
}

// Protección de auto-desactivación
if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $id_usuario) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propio usuario activo.']);
    exit();
}

$stmt = $conn->prepare("UPDATE usuarios SET activo = 0 WHERE id_usuario = ?");
$stmt->bind_param("i", $id_usuario);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario dado de baja correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al dar de baja al usuario.']);
}

$stmt->close();
$conn->close();
?>