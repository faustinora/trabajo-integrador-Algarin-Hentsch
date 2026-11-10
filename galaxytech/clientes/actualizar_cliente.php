<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}

require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $id_cliente = intval($_POST['id_cliente']);
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    $conn = getConnection();

    $sql = "UPDATE clientes SET nombre = ?, apellido = ?, telefono = ?, direccion = ? WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("ssssi", $nombre, $apellido, $telefono, $direccion, $id_cliente);

    if ($stmt->execute()) {
        header("Location: lista_cliente.php?exito=actualizacion_correcta");
        exit();
    } else {
        echo "Error al actualizar el cliente: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
} else {
    header("Location: lista_cliente.php");
    exit();
}
?>