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
$estado = $_GET['status'] ?? '1';

$sql = "SELECT * FROM productos WHERE 1=1";

if ($estado !== 'todos') {
    $estadoInt = (int)$estado;
    $sql .= " AND activo = $estadoInt";
}

$params = [];
$types = "";

if (!empty($busqueda)) {
    $sql .= " AND (codigo LIKE ? OR nombre LIKE ? OR descripcion LIKE ?)";
    $term = "%$busqueda%";
    $params = [$term, $term, $term];
    $types = "sss";
}

$sql .= " ORDER BY nombre ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

$productos = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $productos[] = $fila;
    }
}

echo json_encode($productos, JSON_UNESCAPED_UNICODE);
$conn->close();
?>