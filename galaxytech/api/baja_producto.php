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

if ($id_producto <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de producto inválido."]);
    exit();
}

$sql = "UPDATE productos SET activo = 0 WHERE id_producto = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_producto);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Producto dado de baja."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al dar de baja."]);
}

$stmt->close();
$conn->close();
?>