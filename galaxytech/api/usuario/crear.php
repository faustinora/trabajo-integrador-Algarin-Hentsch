<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave'] ?? '');
    $rol     = trim($_POST['rol'] ?? '');

    if ($usuario === '' || $clave === '' || $rol === '') {
        echo json_encode(['success' => false, 'message' => 'Completá todos los campos.']);
        exit();
    }

    $conn = getConnection();
    $clave_hash = password_hash($clave, PASSWORD_DEFAULT);

    // Consulta con prepared statements (MySQLi)
    $sql = "INSERT INTO usuarios (usuario, clave, rol, activo) VALUES (?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("sss", $usuario, $clave_hash, $rol);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al registrar en la base de datos: ' . $stmt->error]);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la preparación de la consulta.']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
}
?>