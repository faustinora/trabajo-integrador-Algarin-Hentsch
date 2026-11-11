<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();

$id_cliente = isset($_POST['id_cliente']) ? intval($_POST['id_cliente']) : 0;
$nombre     = trim($_POST['nombre'] ?? '');
$apellido   = trim($_POST['apellido'] ?? '')
$telefono   = trim($_POST['telefono'] ?? '');
$email      = trim($_POST['email'] ?? '');

if ($nombre === '' || $apellido === '' || $telefono === '' || $email === '') {
    echo json_encode(["status" => "error", "mensaje" => "Todos los campos son obligatorios."]);
    exit();
}

if ($id_cliente > 0) {
    $sql = "UPDATE clientes SET nombre = ?, apellido = ?, telefono = ?, email = ? WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $apellido, $telefono, $email, $id_cliente);
} else {
    $sql = "INSERT INTO clientes (nombre, apellido, telefono, email, activo) VALUES (?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssss", $nombre, $apellido, $telefono, $email);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Guardado correctamente."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al guardar en la base de datos."]);
}

$stmt->close();
$conn->close();
?>