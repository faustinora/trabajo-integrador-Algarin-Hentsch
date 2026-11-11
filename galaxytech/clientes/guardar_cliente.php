<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}

require_once '../config/conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nombre = trim($_POST['nombre']);
    $apellido = trim($_POST['apellido']);
    $telefono = trim($_POST['telefono']);
    $direccion = trim($_POST['direccion']);

    $conn = getConnection();

    $sql = "INSERT INTO clientes (nombre, apellido, telefono, direccion, activo) VALUES (?, ?, ?, ?, 1)";
    $stmt = $conn->prepare($sql);
    
    $stmt->bind_param("ssss", $nombre, $apellido, $telefono, $direccion);

    if ($stmt->execute()) {

    $cliente_nuevo = $stmt->insert_id;
    $stmt->close();

    $usuario_nombre = $nombre;
    $clave_hash = password_hash($telefono, PASSWORD_DEFAULT);
    $rol = 'cliente';
    $activo = 1;

    $sql_usuario = "INSERT INTO usuarios (id_cliente, usuario, clave, rol, activo) VALUES (?,?,?,?,?)";
    $stmt_usuario = $conn->prepare($sql_usuario);
    $stmt_usuario->bind_param("isssi", $cliente_nuevo, $usuario_nombre, $clave_hash, $rol, $activo);

    if ( $stmt_usuario->execute()){
        $stmt_usuario->close();
        $conn->close();

        header("Location: lista_cliente.php?exito=alta_correcta");
        exit();
    } else {
        echo "Error al registrar el usuario: " . $stmt_usuario->error;
        $stmt_usuario->close();
        $conn->close();
    }

 }else{
    echo "Error al registrar el cliente: " . $stmt->error;
    $stmt->close();
    $conn->close();
 }

}else{
    header("Location: lista_cliente.php");
    exit();
}
?>
