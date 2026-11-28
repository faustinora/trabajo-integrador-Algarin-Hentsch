<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;
$id_servicio = (int)($datos['id_servicio'] ?? 0);

if ($id_servicio <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de servicio no válido.']);
    exit();
}

$stmt = $conn->prepare("UPDATE servicios SET activo = 0 WHERE id_servicio = ? AND activo = 1");
$stmt->bind_param("i", $id_servicio);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Servicio dado de baja correctamente.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'El servicio no existe o ya fue dado de baja.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al dar de baja.']);
}

$stmt->close();
$conn->close();
?>