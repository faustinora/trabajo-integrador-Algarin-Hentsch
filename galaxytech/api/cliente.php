<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$sql = "SELECT id_cliente, nombre, apellido, telefono, email FROM clientes WHERE activo = 1";
$resultado = $conn->query($sql);

$clientes = [];
while ($row = $resultado->fetch_assoc()) {
    $clientes[] = $row;
}

echo json_encode(["status" => "success", "data" => $clientes]);
$conn->close();
?>