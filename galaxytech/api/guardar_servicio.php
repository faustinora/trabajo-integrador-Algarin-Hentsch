<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$id_servicio = isset($_POST['id_servicio']) ? intval($_POST['id_servicio']) : 0;
$id_cliente  = intval($_POST['id_cliente'] ?? 0);
$equipo      = trim($_POST['equipo'] ?? '');
$descripcion = trim($_POST['descripcion'] ?? '');
$estado      = trim($_POST['estado'] ?? 'Pendiente');

if ($id_cliente === 0 || $equipo === '' || $descripcion === '') {
    echo json_encode(["status" => "error", "mensaje" => "Completá los campos obligatorios."]);
    exit();
}

if ($id_servicio > 0) {
    $sql = "UPDATE servicios SET id_cliente = ?, equipo = ?, descripcion = ?, estado = ? WHERE id_servicio = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isssi", $id_cliente, $equipo, $descripcion, $estado, $id_servicio);
} else {
    $sql = "INSERT INTO servicios (id_cliente, equipo, descripcion, estado, activo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("isss", $id_cliente, $equipo, $descripcion, $estado);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Servicio guardado."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al guardar."]);
}

$stmt->close();
$conn->close();
?>