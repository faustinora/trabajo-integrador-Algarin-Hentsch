<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit();
}
require_once '../../config/conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    if ($nombre === '' || $apellido === '') {
        echo json_encode(["success" => false, "message" => "Campos obligatorios vacíos."]);
        exit();
    }

    $conn = getConnection();
    $sql = "INSERT INTO clientes (nombre, apellido, telefono, direccion, activo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $apellido, $telefono, $direccion);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
}
?>