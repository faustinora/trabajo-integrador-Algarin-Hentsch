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

// Obtener el ID del usuario logueado desde la sesión
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0;
if ($id_usuario <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión de usuario no válida.']);
    exit();
}

$datos = json_decode(file_get_contents('php://input'), true);
$id_cliente = (int)($datos['id_cliente'] ?? 0);
// Recoger id_servicio si viene especificado, de lo contrario se guarda como NULL
$id_servicio = !empty($datos['id_servicio']) ? (int)$datos['id_servicio'] : null; 
$productos = $datos['productos'] ?? []; // Array de ítems: [{id_producto, cantidad}, ...]

if ($id_cliente <= 0 || empty($productos) || !is_array($productos)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos incompletos para registrar la venta.']);
    exit();
}

// INICIAR TRANSACCIÓN SQL
$conn->begin_transaction();

try {
    $totalVenta = 0;
    $itemsValidados = [];

    // 1. Validar stock y fijar precios unitarios reales desde la base de datos
    foreach ($productos as $item) {
        $id_producto = (int)($item['id_producto'] ?? 0);
        $cantidad = (int)($item['cantidad'] ?? 0);

        if ($id_producto <= 0 || $cantidad <= 0) {
            throw new Exception("Producto o cantidad inválida.");
        }

        $stmtProd = $conn->prepare("SELECT precio, stock FROM productos WHERE id_producto = ? AND activo = 1 FOR UPDATE");
        $stmtProd->bind_param("i", $id_producto);
        $stmtProd->execute();
        $prod = $stmtProd->get_result()->fetch_assoc();
        $stmtProd->close();

        if (!$prod) {
            throw new Exception("El producto ID $id_producto no existe o está inactivo.");
        }

        if ((int)$prod['stock'] < $cantidad) {
            throw new Exception("Stock insuficiente para el producto ID $id_producto (Disponible: " . $prod['stock'] . ").");
        }

        $precioUnitario = (float)$prod['precio'];
        $subtotal = $precioUnitario * $cantidad;
        $totalVenta += $subtotal;

        $itemsValidados[] = [
            'id_producto' => $id_producto,
            'cantidad' => $cantidad,
            'precio_unitario' => $precioUnitario,
            'nuevo_stock' => (int)$prod['stock'] - $cantidad
        ];
    }

    // 2. Insertar cabecera en la tabla 'ventas' (Incluyendo id_servicio y estado = 1)
    if ($id_servicio !== null) {
        $stmtVenta = $conn->prepare("INSERT INTO ventas (id_cliente, id_usuario, id_servicio, total, fecha_venta, estado) VALUES (?, ?, ?, ?, NOW(), 1)");
        $stmtVenta->bind_param("iiid", $id_cliente, $id_usuario, $id_servicio, $totalVenta);
    } else {
        $stmtVenta = $conn->prepare("INSERT INTO ventas (id_cliente, id_usuario, id_servicio, total, fecha_venta, estado) VALUES (?, ?, NULL, ?, NOW(), 1)");
        $stmtVenta->bind_param("iid", $id_cliente, $id_usuario, $totalVenta);
    }
    
    $stmtVenta->execute();
    $id_venta = $conn->insert_id;
    $stmtVenta->close();

    // 3. Insertar en 'detalle_ventas' y actualizar el stock de cada producto
    $stmtDetalle = $conn->prepare("INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
    $stmtStock = $conn->prepare("UPDATE productos SET stock = ? WHERE id_producto = ?");

    foreach ($itemsValidados as $item) {
        $stmtDetalle->bind_param("iiid", $id_venta, $item['id_producto'], $item['cantidad'], $item['precio_unitario']);
        $stmtDetalle->execute();

        $stmtStock->bind_param("ii", $item['nuevo_stock'], $item['id_producto']);
        $stmtStock->execute();
    }

    $stmtDetalle->close();
    $stmtStock->close();

    // CONFIRMAR TRANSACCIÓN
    $conn->commit();
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Venta registrada con éxito.']);

} catch (Exception $e) {
    // REVERTIR CAMBIOS SI OCURRE UN ERROR
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>