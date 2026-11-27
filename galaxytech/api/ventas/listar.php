<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario'])) {
    echo json_encode([]);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$busqueda = trim($_GET['q'] ?? '');

$sqlBase = "SELECT v.id_venta,
                   MIN(dv.id_servicio) AS id_servicio,
                   DATE_FORMAT(v.fecha_venta, '%d/%m/%Y %H:%i') AS fecha_venta,
                   c.apellido, c.nombre, u.usuario AS vendedor, v.total
            FROM ventas v
            LEFT JOIN detalle_ventas dv ON v.id_venta = dv.id_venta
            LEFT JOIN clientes c ON v.id_cliente = c.id_cliente
            LEFT JOIN usuarios u ON v.id_usuario = u.id_usuario
            WHERE v.estado = 1";

if (!empty($busqueda)) {
    $sqlBase .= " AND (c.apellido LIKE ? OR c.nombre LIKE ? OR u.usuario LIKE ? OR DATE_FORMAT(v.fecha_venta, '%d/%m/%Y %H:%i') LIKE ?)";
    $sqlBase .= " GROUP BY v.id_venta, v.fecha_venta, c.apellido, c.nombre, u.usuario, v.total ORDER BY v.id_venta DESC";
    $stmt = $conn->prepare($sqlBase);
    $term = "%$busqueda%";
    $stmt->bind_param("ssss", $term, $term, $term, $term);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $sqlBase .= " GROUP BY v.id_venta, v.fecha_venta, c.apellido, c.nombre, u.usuario, v.total ORDER BY v.id_venta DESC";
    $res = $conn->query($sqlBase);
}

$ventas = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $ventas[] = $fila;
    }
}

echo json_encode($ventas, JSON_UNESCAPED_UNICODE);
$conn->close();
?>