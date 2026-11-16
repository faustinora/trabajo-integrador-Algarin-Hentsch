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

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0;
if ($id_usuario <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión de usuario inválida.']);
    exit();
}

$datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$id_cliente = (int)($datos['id_cliente'] ?? 0);
$equipo_descripcion = trim($datos['equipo_descripcion'] ?? '');
$diagnostico = trim($datos['diagnostico'] ?? '');
$costo = (float)($datos['costo'] ?? 0);
$estado = trim($datos['estado'] ?? 'pendiente');

if ($id_cliente <= 0 || empty($equipo_descripcion) || $costo < 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cliente, descripción del equipo y costo son obligatorios.']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO servicios (id_cliente, id_usuario, equipo_descripcion, diagnostico, costo, estado, fecha_ingreso, activo) VALUES (?, ?, ?, ?, ?, ?, NOW(), 1)");
$stmt->bind_param("iissds", $id_cliente, $id_usuario, $equipo_descripcion, $diagnostico, $costo, $estado);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Servicio registrado exitosamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al registrar el servicio en la base de datos.']);
}

$stmt->close();
$conn->close();
?>