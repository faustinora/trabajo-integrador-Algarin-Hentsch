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

$nombre = trim($datos['nombre'] ?? '');
$apellido = trim($datos['apellido'] ?? '');
$telefono = trim($datos['telefono'] ?? '');
$email = trim($datos['email'] ?? '');

if (empty($nombre) || empty($apellido) || empty($telefono)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Nombre, apellido y teléfono son obligatorios.']);
    exit();
}

// 2. Controlar que el teléfono contenga ÚNICAMENTE números
if (!preg_match('/^[0-9]+$/', $telefono)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El teléfono debe contener únicamente números.']);
    exit();
}

// 3. Validar formato de email
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'El formato del correo electrónico no es válido.']);
    exit();
}

// 4. Controlar que no se repita el teléfono o el email (datos únicos de contacto)
$stmtCheck = $conn->prepare("SELECT id_cliente FROM clientes WHERE telefono = ? OR email = ?");
$stmtCheck->bind_param("ss", $telefono, $email);
$stmtCheck->execute();
$stmtCheck->store_result();

if ($stmtCheck->num_rows > 0) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Ya existe un cliente registrado con el mismo número de teléfono o correo electrónico.']);
    $stmtCheck->close();
    $conn->close();
    exit();
}
$stmtCheck->close();

$stmt = $conn->prepare("INSERT INTO clientes (nombre, apellido, telefono, email, activo) VALUES (?, ?, ?, ?, 1)");
$stmt->bind_param("ssss", $nombre, $apellido, $telefono, $email);

if ($stmt->execute()) {
    http_response_code(201);
    echo json_encode(['success' => true, 'message' => 'Cliente registrado exitosamente.']);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos.']);
}

$stmt->close();
$conn->close();
?>