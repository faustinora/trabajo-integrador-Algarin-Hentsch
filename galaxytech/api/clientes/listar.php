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

$sql = "SELECT * FROM clientes WHERE 1=1";

if ($estado !== 'todos') {
    $estadoInt = (int)$estado;
    $sql .= " AND activo = $estadoInt";
}

$params = [];
$types = "";

if (!empty($busqueda)) {
    $sql .= " AND (nombre LIKE ? OR apellido LIKE ? OR telefono LIKE ? OR email LIKE ?)";
    $term = "%$busqueda%";
    $params = [$term, $term, $term, $term];
    $types = "ssss";
}

$sql .= " ORDER BY id_cliente ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

$clientes = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $clientes[] = $fila;
    }
}

echo json_encode($clientes, JSON_UNESCAPED_UNICODE);
$conn->close();
?>