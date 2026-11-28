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

$datos = json_decode(file_get_contents('php://input'), true) ?? $_POST;

$id_cliente = (int)($datos['id_cliente'] ?? 0);
$nombre = trim($datos['nombre'] ?? '');
$apellido = trim($datos['apellido'] ?? '');
$telefono = trim($datos['telefono'] ?? '');
$email = trim($datos['email'] ?? '');

if ($id_cliente <= 0 || empty($nombre) || empty($apellido) || empty($telefono)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre, apellido y teléfono son obligatorios.']);
    exit();
}

if (!preg_match('/^[0-9]+$/', $telefono)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El teléfono debe contener únicamente números.']);
    exit();
}

if ($email !== '' && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
    exit();
}

$stmt = $conn->prepare("UPDATE clientes SET nombre = ?, apellido = ?, telefono = ?, email = ? WHERE id_cliente = ?");
$stmt->bind_param("ssssi", $nombre, $apellido, $telefono, $email, $id_cliente);

if ($stmt->execute()) {
    if ($stmt->affected_rows > 0) {
        echo json_encode(['success' => true, 'message' => 'Cliente actualizado.']);
    } else {
        http_response_code(404);
        echo json_encode(['success' => false, 'message' => 'No se encontró el cliente o no hubo cambios.']);
    }
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al actualizar.']);
}

$stmt->close();
$conn->close();
?>