<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(["status" => "error", "mensaje" => "No autorizado"]);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

// 1. Indicadores (KPIs) principales
$totalClientes = $conn->query("SELECT COUNT(*) as total FROM clientes WHERE activo = 1 AND id_cliente != 1")->fetch_assoc()['total'] ?? 0;
$stockBajo = $conn->query("SELECT COUNT(*) as total FROM productos WHERE activo = 1 AND stock <= 3")->fetch_assoc()['total'] ?? 0;
$serviciosPendientes = $conn->query("SELECT COUNT(*) as total FROM servicios WHERE activo = 1 AND estado = 'pendiente'")->fetch_assoc()['total'] ?? 0;
$totalVentasMonto = $conn->query("SELECT SUM(total) as suma FROM ventas WHERE estado = 1")->fetch_assoc()['suma'] ?? 0;

// 1b. Lista detallada de productos con stock bajo (para el modal)
$sqlListaStockBajo = "SELECT codigo, nombre, stock 
                      FROM productos 
                      WHERE activo = 1 AND stock <= 3 
                      ORDER BY stock ASC";
$resListaStock = $conn->query($sqlListaStockBajo);
$listaStockBajo = [];
if ($resListaStock) {
    while ($row = $resListaStock->fetch_assoc()) {
        $listaStockBajo[] = $row;
    }
}

// 2. Informe 1: Top 5 Productos más vendidos
$sqlProductosTop = "SELECT p.nombre, SUM(dv.cantidad) AS total_vendido
                    FROM detalle_ventas dv
                    INNER JOIN productos p ON dv.id_producto = p.id_producto
                    INNER JOIN ventas v ON dv.id_venta = v.id_venta
                    WHERE v.estado = 1 AND p.activo = 1
                    GROUP BY p.id_producto, p.nombre
                    ORDER BY total_vendido DESC
                    LIMIT 5";
$resProductosTop = $conn->query($sqlProductosTop);
$productosTop = [];
if ($resProductosTop) {
    while ($row = $resProductosTop->fetch_assoc()) {
        $productosTop[] = $row;
    }
}

// 3. Informe 2: Top 5 Clientes con mayor actividad (Compras + Servicios)
$sqlClientesTop = "SELECT c.nombre, c.apellido,
                          SUM(op.es_venta) AS total_ventas,
                          SUM(op.es_servicio) AS total_servicios,
                          COUNT(*) AS total_operaciones
                   FROM clientes c
                   INNER JOIN (
                       SELECT id_cliente, 1 AS es_venta, 0 AS es_servicio
                       FROM ventas
                       WHERE estado = 1
                       UNION ALL
                       SELECT id_cliente, 0 AS es_venta, 1 AS es_servicio
                       FROM servicios
                       WHERE activo = 1
                   ) op ON c.id_cliente = op.id_cliente
                   WHERE c.activo = 1 AND c.id_cliente != 1
                   GROUP BY c.id_cliente, c.nombre, c.apellido
                   ORDER BY total_operaciones DESC
                   LIMIT 5";
$resClientesTop = $conn->query($sqlClientesTop);
$clientesTop = [];
if ($resClientesTop) {
    while ($row = $resClientesTop->fetch_assoc()) {
        $clientesTop[] = $row;
    }
}

echo json_encode([
    "status" => "success",
    "kpis" => [
        "clientes" => $totalClientes,
        "stock_bajo" => $stockBajo,
        "servicios_pendientes" => $serviciosPendientes,
        "monto_ventas" => $totalVentasMonto
    ],
    "lista_stock_bajo" => $listaStockBajo,
    "informe_productos" => $productosTop,
    "informe_clientes" => $clientesTop
], JSON_UNESCAPED_UNICODE);

$conn->close();
?>