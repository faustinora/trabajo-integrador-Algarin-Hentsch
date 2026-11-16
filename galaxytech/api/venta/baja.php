<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$id_venta = isset($_POST['id_venta']) ? intval($_POST['id_venta']) : 0;

if ($id_venta <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de venta inválido."]);
    exit();
}

$sql = "UPDATE ventas SET activo = 0 WHERE id_venta = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_venta);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Venta dada de baja."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al dar de baja."]);
}

$stmt->close();
$conn->close();
?>