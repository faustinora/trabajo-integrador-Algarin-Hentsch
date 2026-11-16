<?php
session_start();
header('Content-Type: application/json');
if (!isset($_SESSION['usuario'])) {
    echo json_encode(["success" => false, "message" => "No autorizado"]);
    exit();
}
require_once '../../config/conexion.php';
$data = json_decode(file_get_contents("php://input"), true);
$id_cliente = intval($data['id_cliente'] ?? 0);

if ($id_cliente > 0) {
    $conn = getConnection();
    // Baja lógica exigida por la cátedra
    $sql = "UPDATE clientes SET activo = 0 WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);
    if ($stmt->execute()) {
        echo json_encode(["success" => true]);
    } else {
        echo json_encode(["success" => false, "message" => $stmt->error]);
    }
    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["success" => false, "message" => "ID inválido"]);
}
?>