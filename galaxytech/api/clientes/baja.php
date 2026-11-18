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

$datos = json_decode(file_get_contents('php://input'), true);
$id_cliente = (int)($datos['id_cliente'] ?? 0);

if ($id_cliente <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de cliente no válido.']);
    exit();
}

$stmt = $conn->prepare("UPDATE clientes SET activo = 0 WHERE id_cliente = ?");
$stmt->bind_param("i", $id_cliente);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Cliente dado de baja correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al dar de baja al cliente.']);
}

$stmt->close();
$conn->close();
?>