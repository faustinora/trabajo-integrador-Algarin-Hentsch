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

$datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$usuario = trim($datos['usuario'] ?? '');
$clave   = trim($datos['clave'] ?? '');
$rol     = trim($datos['rol'] ?? '');

if (empty($usuario) || empty($clave) || empty($rol)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios.']);
    exit();
}

// Verificar duplicados
$stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ?");
$stmtCheck->bind_param("s", $usuario);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    $stmtCheck->close();
    $conn->close();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso.']);
    exit();
}
$stmtCheck->close();

$clave_hash = password_hash($clave, PASSWORD_DEFAULT);

$stmt = $conn->prepare("INSERT INTO usuarios (usuario, clave, rol, activo) VALUES (?, ?, ?, 1)");
$stmt->bind_param("sss", $usuario, $clave_hash, $rol);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Usuario registrado exitosamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
}

$stmt->close();
$conn->close();
?>