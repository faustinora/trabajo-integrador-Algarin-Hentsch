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

$codigo = trim($datos['codigo'] ?? '');
$nombre = trim($datos['nombre'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$precio = (float)($datos['precio'] ?? 0);
$stock = (int)($datos['stock'] ?? 0);

if (empty($codigo) || empty($nombre) || $precio <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Código, nombre y un precio válido son obligatorios.']);
    exit();
}

$stmt = $conn->prepare("INSERT INTO productos (codigo, nombre, descripcion, precio, stock, activo) VALUES (?, ?, ?, ?, ?, 1)");
$stmt->bind_param("sssdi", $codigo, $nombre, $descripcion, $precio, $stock);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Producto registrado exitosamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar. Verifique que el código no esté repetido.']);
}

$stmt->close();
$conn->close();
?>