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

if ($id_cliente > 0) {
    $sql = "UPDATE clientes SET activo = 0 WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success", "mensaje" => "Cliente dado de baja."]);
    } else {
        echo json_encode(["status" => "error", "mensaje" => "No se pudo procesar la baja."]);
    }
    $stmt->close();
} else {
    echo json_encode(["status" => "error", "mensaje" => "ID inválido."]);
}

$conn->close();
?>