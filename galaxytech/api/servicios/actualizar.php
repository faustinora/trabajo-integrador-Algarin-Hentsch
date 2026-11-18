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
$equipo_descripcion = trim($datos['equipo_descripcion'] ?? '');
$diagnostico = trim($datos['diagnostico'] ?? '');
$costo = (float)($datos['costo'] ?? 0);
$estado = trim($datos['estado'] ?? 'pendiente');

if ($id_servicio <= 0 || empty($equipo_descripcion)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos.']);
    exit();
}

$stmt = $conn->prepare("UPDATE servicios SET equipo_descripcion = ?, diagnostico = ?, costo = ?, estado = ? WHERE id = ?");
$stmt->bind_param("ssdsi", $equipo_descripcion, $diagnostico, $costo, $estado, $id_servicio);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Servicio actualizado.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
}

$stmt->close();
$conn->close();
?>