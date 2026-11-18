<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$id_producto = (int)($datos['id_producto'] ?? 0);
$codigo = trim($datos['codigo'] ?? '');
$nombre = trim($datos['nombre'] ?? '');
$descripcion = trim($datos['descripcion'] ?? '');
$precio = (float)($datos['precio'] ?? 0);
$stock = (int)($datos['stock'] ?? 0);

// Validar ID, campos obligatorios, precio y stock mayores a 0
if ($id_producto <= 0 || empty($codigo) || empty($nombre) || $precio <= 0 || $stock <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos o inválidos. El precio y el stock deben ser mayores a 0.']);
    exit();
}

// Verificar que el código o nombre no estén siendo usados por OTRO producto
$stmtCheck = $conn->prepare("SELECT id_producto FROM productos WHERE (codigo = ? OR nombre = ?) AND id_producto != ?");
$stmtCheck->bind_param("ssi", $codigo, $nombre, $id_producto);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ya existe otro producto con el mismo código o nombre.']);
    $stmtCheck->close();
    $conn->close();
    exit();
}
$stmtCheck->close();

// Actualizar producto (Tipos correctos: sssdii -> string, string, string, double, int, int)
$stmt = $conn->prepare("UPDATE productos SET codigo = ?, nombre = ?, descripcion = ?, precio = ?, stock = ? WHERE id_producto = ?");
$stmt->bind_param("sssdii", $codigo, $nombre, $descripcion, $precio, $stock, $id_producto);

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Producto actualizado correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el producto.']);
}

$stmt->close();
$conn->close();
?>