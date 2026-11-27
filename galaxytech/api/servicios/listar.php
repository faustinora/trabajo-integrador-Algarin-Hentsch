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
$estadoFiltro = strtolower(trim($_GET['status'] ?? 'todos'));
$estadosValidos = ['pendiente', 'en proceso', 'completado', 'entregado'];

$sql = "SELECT s.id_servicio AS id_servicio, s.equipo_descripcion, s.diagnostico, s.costo, s.estado,
               DATE_FORMAT(s.fecha_ingreso, '%d/%m/%Y %H:%i') AS fecha_ingreso,
               DATE_FORMAT(s.fecha_entrega, '%d/%m/%Y %H:%i') AS fecha_entrega, s.activo,
               c.id_cliente, c.nombre AS cliente_nombre, c.apellido AS cliente_apellido,
               u.id_usuario, u.usuario AS vendedor_usuario
        FROM servicios s
        INNER JOIN clientes c ON s.id_cliente = c.id_cliente
        INNER JOIN usuarios u ON s.id_usuario = u.id_usuario
        WHERE s.activo = 1";

$params = [];
$types = "";

if ($estadoFiltro !== 'todos' && in_array($estadoFiltro, $estadosValidos, true)) {
    $sql .= " AND s.estado = ?";
    $params[] = $estadoFiltro;
    $types .= "s";
}

if (!empty($busqueda)) {
    $sql .= " AND (s.equipo_descripcion LIKE ? OR s.diagnostico LIKE ? OR c.apellido LIKE ? OR c.nombre LIKE ?)";
    $term = "%$busqueda%";
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $params[] = $term;
    $types .= "ssss";
}

$sql .= " ORDER BY s.id_servicio DESC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

$servicios = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $servicios[] = $fila;
    }
}

echo json_encode($servicios, JSON_UNESCAPED_UNICODE);
$conn->close();
?>