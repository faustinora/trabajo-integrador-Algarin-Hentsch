<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$codigo      = trim($_POST['codigo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$precio      = floatval($_POST['precio'] ?? 0);
$stock       = intval($_POST['stock'] ?? 0);

if ($codigo === '' || $descripcion === '' || $precio <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "Completá los campos obligatorios y un precio válido."]);
    exit();
}

if ($id_producto > 0) {
    $sql = "UPDATE productos SET codigo = ?, descripcion = ?, precio = ?, stock = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdii", $codigo, $descripcion, $precio, $stock, $id_producto);
} else {
    $sql = "INSERT INTO productos (codigo, descripcion, precio, stock, activo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssdi", $codigo, $descripcion, $precio, $stock);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Producto guardado."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al guardar."]);
}

$stmt->close();
$conn->close();
?>