<?php
session_start();
header('Content-Type: application/json; charset=UTF-8');

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado.']);
    exit();
}

require_once '../../config/conexion.php';
$conn = getConnection();

$busqueda = trim($_GET['q'] ?? '');
$estado = $_GET['status'] ?? '1';

$sql = "SELECT id_usuario, usuario, rol, activo FROM usuarios WHERE 1=1";
$params = [];
$types = "";

if ($estado !== 'todos') {
    $estadoInt = ((int)$estado === 0) ? 0 : 1;
    $sql .= " AND activo = ?";
    $params[] = $estadoInt;
    $types .= "i";
}

if (!empty($busqueda)) {
    $sql .= " AND (usuario LIKE ? OR rol LIKE ?)";
    $term = "%$busqueda%";
    $params[] = $term;
    $params[] = $term;
    $types .= "ss";
}

$sql .= " ORDER BY id_usuario ASC";

if (!empty($params)) {
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    $res = $stmt->get_result();
} else {
    $res = $conn->query($sql);
}

$usuarios = [];
if ($res) {
    while ($fila = $res->fetch_assoc()) {
        $usuarios[] = $fila;
    }
}

echo json_encode($usuarios, JSON_UNESCAPED_UNICODE);
$conn->close();
?>