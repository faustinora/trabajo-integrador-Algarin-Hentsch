<?php
session_start();
header('Content-Type: application/json');
require_once '../../config/conexion.php';

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit();
}

// Leer el JSON enviado por fetch (body: JSON.stringify)
$data = json_decode(file_get_contents("php://input"), true);
$id_usuario = intval($data['id_usuario'] ?? 0);
$nuevo_estado = intval($data['activo'] ?? 1);

// Protección contra auto-desactivación
if (isset($_SESSION['id_usuario']) && $_SESSION['id_usuario'] == $id_usuario && $nuevo_estado == 0) {
    echo json_encode(['success' => false, 'message' => 'No puedes desactivar tu propio usuario activo.']);
    exit();
}

if ($id_usuario > 0) {
    $conn = getConnection();
    
    // Baja lógica con prepared statements
    $sql = "UPDATE usuarios SET activo = ? WHERE id_usuario = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt) {
        $stmt->bind_param("ii", $nuevo_estado, $id_usuario);

        if ($stmt->execute()) {
            echo json_encode(['success' => true]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al actualizar el estado.']);
        }

        $stmt->close();
    } else {
        echo json_encode(['success' => false, 'message' => 'Error en la consulta preparada.']);
    }

    $conn->close();
} else {
    echo json_encode(['success' => false, 'message' => 'ID de usuario inválido.']);
}
?>