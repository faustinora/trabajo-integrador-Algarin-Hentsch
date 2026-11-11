<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$sql = "SELECT s.id AS id_servicio, s.equipo_descripcion AS equipo, s.diagnostico AS descripcion, s.estado, s.fecha_ingreso AS fecha, s.costo,
               c.apellido, c.nombre, c.id_cliente 
        FROM servicios s 
        INNER JOIN clientes c ON s.id_cliente = c.id_cliente 
        WHERE s.activo = 1 
        ORDER BY s.id DESC";

$resultado = $conn->query($sql);

$servicios = [];
while ($row = $resultado->fetch_assoc()) {
    $servicios[] = $row;
}

echo json_encode(["status" => "success", "data" => $servicios]);
$conn->close();
?>