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

$id_producto = (int)($datos['id_producto'] ?? 0);
$codigo = trim($datos['codigo'] ?? '');
$nombre = trim($datos['nombre'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$precio = (float)($datos['precio'] ?? 0);
$stock = (int)($datos['stock'] ?? 0);

if ($id_producto <= 0 || empty($codigo) || empty($nombre) || $precio <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos.']);
    exit();
}

$stmt = $conn->prepare("UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id_producto = ?");
$stmt->bind_param("sssdii", $codigo, $nombre, $descripcion, $precio, $stock, $id_producto); 

$stmt->close();

$stmt = $conn->prepare("UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id_producto = ?");
$stmt->bind_param("sssdsi", $codigo, $nombre, $descripcion, $precio, $stock, $id_producto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto actualizado.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
}

$stmt->close();
$conn->close();
?>