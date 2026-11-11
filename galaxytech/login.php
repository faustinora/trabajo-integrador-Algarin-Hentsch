<?php
session_start();

require_once 'config/conexion.php'; 

$error = "";

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario = trim($_POST['usuario'] ?? '');
    $clave   = trim($_POST['clave'] ?? '');

    if ($usuario !== '' && $clave !== '') {
        $conn = getConnection(); 

        $sql = "SELECT id_usuario, usuario, clave, rol FROM usuarios WHERE usuario = ? AND activo = 1";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $usuario);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($row = $resultado->fetch_assoc()) {
            if (password_verify($clave, $row['clave'])) {
                $_SESSION['usuario'] = $row['usuario'];
                $_SESSION['rol'] = $row['rol'];
                header("Location: principal.php");
                exit();
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El usuario no existe.";
        }
        
        $stmt->close();
        $conn->close();
    } else {
        $error = "Completá todos los campos.";
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login - GalaxyTech</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="login-body">
    <div class="login-container">
        <h2>Iniciar Sesión</h2>
        
        <?php if ($error !== ""): ?>
            <p style="color: red;"><?php echo $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Usuario:</label><br>
            <input type="text" name="usuario" required><br><br>

            <label>Contraseña:</label><br>
            <input type="password" name="clave" required><br><br>

            <button type="submit">Ingresar</button>
        </form>
    </div>
</body>
</html>