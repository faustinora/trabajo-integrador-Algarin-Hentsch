<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['usuario'])) {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$id_producto = isset($_POST['id_producto']) ? intval($_POST['id_producto']) : 0;
$codigo      = trim($_POST['codigo'] ?? '');
$nombre      = trim($_POST['nombre'] ?? ''); 
$descripcion = trim($_POST['descripcion'] ?? '');
$precio      = floatval($_POST['precio'] ?? 0);
$stock       = intval($_POST['stock'] ?? 0);

if ($codigo === '' || $nombre === '' || $precio <= 0) {
    echo json_encode(["status" => "error", "mensaje" => "Completá el código, el nombre y un precio válido."]);
    exit();
}

if ($id_producto > 0) {
    // Actualizar producto existente
    $sql = "UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id_producto = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdii", $codigo, $nombre, $descripcion, $precio, $stock, $id_producto);
} else {
    // Insertar producto nuevo
    $sql = "INSERT INTO productos (codigo, nombre, descripcion, precio, stock, activo) VALUES (?, ?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sssdi", $codigo, $nombre, $descripcion, $precio, $stock);
}

if ($stmt->execute()) {
    echo json_encode(["status" => "success", "mensaje" => "Producto guardado correctamente."]);
} else {
    echo json_encode(["status" => "error", "mensaje" => "Error al guardar en la base de datos: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>