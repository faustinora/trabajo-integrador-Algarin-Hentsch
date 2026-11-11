<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$id_cliente = intval($_POST['id_cliente'] ?? 0);
$total      = floatval($_POST['total'] ?? 0);

if ($id_cliente === 0 || $total <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "Seleccioná un cliente y un total válido."]);
    exit();
}

$sql = "INSERT INTO ventas (id_cliente, total, activo) VALUES (?, ?, 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("id", $id_cliente, $total);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Venta registrada."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al registrar."]);
}

$stmt->close();
$conn->close();
?>