<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$id_servicio = isset($_POST['id_servicio']) ? intval($_POST['id_servicio']) : 0;

if ($id_servicio <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "ID de servicio inválido."]);
    exit();
}

$sql = "UPDATE servicios SET activo = 0 WHERE id_servicio = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_servicio);

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Servicio dado de baja correctamente."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al dar de baja el servicio."]);
}

$stmt->close();
$conn->close();
?>