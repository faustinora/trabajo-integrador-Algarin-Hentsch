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

$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id'] ?? 0;
if ($id_usuario <= 0) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'Sesión de usuario no válida.']);
    exit();
}

$datos = json_decode(file_get_contents('php://input'), true);
$id_cliente = (int)($datos['id_cliente'] ?? 0);
$id_servicio = !empty($datos['id_servicio']) ? (int)$datos['id_servicio'] : null; 
$productos = $datos['productos'] ?? [];

if ($id_cliente <= 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Cliente no válido.']);
    exit();
}

$conn->begin_transaction();

try {
    $totalVenta = 0;
    $itemsValidados = [];

    // 1. Si viene un servicio, obtener su costo oficial de la base de datos
    if ($id_servicio !== null) {
        $stmtServ = $conn->prepare("SELECT costo FROM servicios WHERE id_servicio = ?");
        $stmtServ->bind_param("i", $id_servicio);
        $stmtServ->execute();
        $resServ = $stmtServ->get_result()->fetch_assoc();
        $stmtServ->close();

        if ($resServ) {
            $totalVenta += (float)$resServ['costo'];
        }
    }

    // 2. Validar productos si es que se agregaron (repuestos opcionales)
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

    if ($totalVenta <= 0 && empty($productos) && $id_servicio === null) {
        throw new Exception("Debe agregar productos o un servicio válido para registrar la venta.");
    }

    // 3. Insertar cabecera en la tabla 'ventas'[cite: 13]
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

    // 4. Insertar detalles y actualizar stock si hay productos[cite: 13]
    if (!empty($itemsValidados)) {
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
    }

    $conn->commit();
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Venta registrada y servicio cobrado con éxito.']);

} catch (Exception $e) {
    $conn->rollback();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}

$conn->close();
?>