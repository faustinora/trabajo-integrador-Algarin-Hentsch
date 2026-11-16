<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit();
}
require_once '../../config/conexion.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_cliente = intval($_POST['id_cliente'] ?? 0);
    $nombre = trim($_POST['nombre'] ?? '');
    $apellido = trim($_POST['apellido'] ?? '');
    $telefono = trim($_POST['telefono'] ?? '');
    $direccion = trim($_POST['direccion'] ?? '');

    $conn = getConnection();
    $sql = "UPDATE clientes SET nombre = ?, apellido = ?, telefono = ?, direccion = ? WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssssi", $nombre, $apellido, $telefono, $direccion, $id_cliente);

    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
}
?>