<?php
session_start();
require_once 'config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $usuario_ingresado = trim($_POST['usuario']);
    $clave_ingresada = trim($_POST['clave']);

    $conn = getConnection();

    $sql = "SELECT id_usuario, usuario, clave, rol, activo FROM usuarios WHERE usuario = ?";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("s", $usuario_ingresado);
    $stmt->execute();
    
    $resultado = $stmt->get_result();

    if ($resultado->num_rows === 1) {
        $row = $resultado->fetch_assoc();

        if ($row['activo'] == 0) {
            die("Error: Este usuario ya no existe (desactivado).");
        }

        if (password_verify($clave_ingresada, $row['clave'])) {
            $_SESSION['id_usuario'] = $row['id_usuario'];
            $_SESSION['usuario'] = $row['usuario'];
            $_SESSION['rol'] = $row['rol'];

            header("Location: panelAdmin.php");
            exit();
        } else {
            echo "Contraseña incorrecta.";
        }
    } else {
        echo "El usuario no existe.";
    }
    
    $stmt->close();
    $conn->close();
}
?>