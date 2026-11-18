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

$datos = json_decode(file_get_contents('php://input'), true);
$id_venta = (int)($datos['id_venta'] ?? 0);

if ($id_venta <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'ID de venta no válido.']);
    exit();
}

$conn->begin_transaction();

try {
    // Verificar si la venta existe y su estado actual
    $stmtCheck = $conn->prepare("SELECT estado FROM ventas WHERE id_venta = ? FOR UPDATE");
    $stmtCheck->bind_param("i", $id_venta);
    $stmtCheck->execute();
    $ventaData = $stmtCheck->get_result()->fetch_assoc();
    $stmtCheck->close();

    if (!$ventaData) {
        throw new Exception("La venta no existe.");
    }
    if ((int)$ventaData['estado'] === 0) {
        throw new Exception("La venta ya se encuentra anulada.");
    }

    // Obtener los detalles para restaurar el stock
    $stmtDetalles = $conn->prepare("SELECT id_producto, cantidad FROM detalle_ventas WHERE id_venta = ?");
    $stmtDetalles->bind_param("i", $id_venta);
    $stmtDetalles->execute();
    $resDetalles = $stmtDetalles->get_result();

    $stmtStockRest = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id_producto = ?");

    while ($det = $resDetalles->fetch_assoc()) {
        $stmtStockRest->bind_param("ii", $det['cantidad'], $det['id_producto']);
        $stmtStockRest->execute();
    }
    $stmtDetalles->close();
    $stmtStockRest->close();

    // Marcar la venta como anulada (estado = 0)
    $stmtBaja = $conn->prepare("UPDATE ventas SET estado = 0 WHERE id_venta = ?");
    $stmtBaja->bind_param("i", $id_venta);
    $stmtBaja->execute();
    $stmtBaja->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Venta anulada y stock restaurado correctamente.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>