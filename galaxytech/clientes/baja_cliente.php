<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}

require_once '../config/conexion.php';

if (isset($_GET['id'])) {
    $id_cliente = intval($_GET['id']);

    $conn = getConnection();

    $sql = "UPDATE clientes SET activo = 0 WHERE id_cliente = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_cliente);

    if ($stmt->execute()){
        $stmt->close();

        $sql_usuario = "UPDATE usuarios SET activo = 0 WHERE id_cliente = ?";
        $stmt_usuario = $conn->prepare($sql_usuario);
        $stmt_usuario->bind_param("i", $id_cliente);

        if ($stmt_usuario->execute()){
            $stmt_usuario->close();
            $conn->close();

            header("Location: lista_cliente.php?exito=baja_correcta");
            exit();
        }else{
            echo "Error al dar de baja al usuario: " . $stmt_usuario->error;
            $stmt_usuario->close();
        }

        $conn->close();
    }else{
        header("Location: lista_cliente.php");
        exit();
    }
}

?>