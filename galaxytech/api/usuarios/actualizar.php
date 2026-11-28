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

$id_usuario = (int)($datos['id_usuario'] ?? 0);
$usuario    = trim($datos['usuario'] ?? '');
$clave      = trim($datos['clave'] ?? '');
$rol        = strtolower(trim($datos['rol'] ?? ''));
$activo     = ((int)($datos['activo'] ?? 1) === 0) ? 0 : 1;
$rolesValidos = ['admin', 'vendedor'];

if ($id_usuario <= 0 || empty($usuario) || !in_array($rol, $rolesValidos, true)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Datos inválidos o incompletos.']);
    exit();
}

if (isset($_SESSION['id_usuario']) && (int)$_SESSION['id_usuario'] === $id_usuario && $activo === 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propio usuario activo.']);
    exit();
}

$stmtCheck = $conn->prepare("SELECT id_usuario FROM usuarios WHERE usuario = ? AND id_usuario != ? LIMIT 1");
$stmtCheck->bind_param("si", $usuario, $id_usuario);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    $stmtCheck->close();
    $conn->close();
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El nombre de usuario ya está en uso por otro registro.']);
    exit();
}
$stmtCheck->close();

if (!empty($clave)) {
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);
    $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, clave = ?, rol = ?, activo = ? WHERE id_usuario = ?");
    $stmt->bind_param("sssii", $usuario, $clave_hash, $rol, $activo, $id_usuario);
} else {
    $stmt = $conn->prepare("UPDATE usuarios SET usuario = ?, rol = ?, activo = ? WHERE id_usuario = ?");
    $stmt->bind_param("ssii", $usuario, $rol, $activo, $id_usuario);
}

if ($stmt->execute()) {
    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar el usuario.']);
}

$stmt->close();
$conn->close();
?>