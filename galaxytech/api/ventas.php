<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$sql = "SELECT v.id_venta, v.total, v.fecha_venta AS fecha, 
               c.apellido, c.nombre, c.id_cliente 
        FROM ventas v 
        INNER JOIN clientes c ON v.id_cliente = c.id_cliente 
        WHERE v.activo = 1 
        ORDER BY v.id_venta DESC";

$resultado = $conn->query($sql);

$ventas = [];
while ($row = $resultado->fetch_assoc()) {
    $ventas[] = $row;
}

echo json_encode(["status" => "success", "data" => $ventas]);
$conn->close();
?>