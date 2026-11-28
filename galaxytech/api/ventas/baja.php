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

    $stmtDetalles = $conn->prepare("SELECT id_producto, id_servicio, cantidad FROM detalle_ventas WHERE id_venta = ?");
    $stmtDetalles->bind_param("i", $id_venta);
    $stmtDetalles->execute();
    $resDetalles = $stmtDetalles->get_result();

    $stmtStockRest = $conn->prepare("UPDATE productos SET stock = stock + ? WHERE id_producto = ?");
    $stmtServReset = $conn->prepare("UPDATE servicios SET estado = 'completado', fecha_entrega = NULL WHERE id_servicio = ? AND activo = 1");

    while ($det = $resDetalles->fetch_assoc()) {
        if (!empty($det['id_producto']) && (int)$det['cantidad'] > 0) {
            $cant = (int)$det['cantidad'];
            $idProd = (int)$det['id_producto'];
            $stmtStockRest->bind_param("ii", $cant, $idProd);
            if (!$stmtStockRest->execute()) {
                throw new Exception("No se pudo restaurar el stock del producto.");
            }
        }
        if (!empty($det['id_servicio'])) {
            $idServ = (int)$det['id_servicio'];
            $stmtServReset->bind_param("i", $idServ);
            if (!$stmtServReset->execute()) {
                throw new Exception("No se pudo revertir el estado del servicio.");
            }
        }
    }
    $stmtDetalles->close();
    $stmtStockRest->close();
    $stmtServReset->close();

    $stmtBaja = $conn->prepare("UPDATE ventas SET estado = 0 WHERE id_venta = ?");
    $stmtBaja->bind_param("i", $id_venta);
    if (!$stmtBaja->execute() || $stmtBaja->affected_rows === 0) {
        throw new Exception("No se pudo anular la venta.");
    }
    $stmtBaja->close();

    $conn->commit();
    echo json_encode(['success' => true, 'message' => 'Venta anulada, stock restaurado y servicio revertido a completado.']);
} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>