<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$sql = "SELECT id_producto, codigo, descripcion, precio, stock FROM productos WHERE activo = 1 ORDER BY descripcion ASC";
$resultado = $conn->query($sql);

$productos = [];
while ($row = $resultado->fetch_assoc()) {
    $productos[] = $row;
}

echo json_encode(["status" => "success", "data" => $productos]);
$conn->close();
?>