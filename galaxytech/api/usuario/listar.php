<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/conexion.php';

// Validación de seguridad por sesión y rol de administrador
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode([]);
    exit();
}

$conn = getConnection();
$sql = "SELECT id_usuario, usuario, rol, activo FROM usuarios";
$resultado = $conn->query($sql);

$usuarios = [];
while ($row = $resultado->fetch_assoc()) {
    $usuarios[] = $row;
}

echo json_encode($usuarios);
$conn->close();
?>