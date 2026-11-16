<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

// Consulta con JOIN para traer los datos del cliente asociado
$sql = "SELECT s.id_servicio, s.equipo, s.descripcion, s.estado, s.activo,
               c.id_cliente, c.nombre, c.apellido 
        FROM servicios s 
        INNER JOIN clientes c ON s.id_cliente = c.id_cliente 
        WHERE s.activo = 1 
        ORDER BY s.id_servicio DESC";

$resultado = $conn->query($sql);

$servicios = [];
while ($row = $resultado->fetch_assoc()) {
    $servicios[] = $row;
}

echo json_encode(["status" => "success", "data" => $servicios]);
$conn->close();
?>