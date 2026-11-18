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
                $_SESSION['id_usuario'] = $row['id_usuario'];
                $_SESSION['usuario']    = $row['usuario'];
                $_SESSION['rol']        = $row['rol'];

                if ($row['rol'] === 'admin') {
                    header("Location: panelAdmin.php");
                } else {
                    header("Location: panelVendedor.php");
                }
                exit();

            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "El usuario no existe o está inactivo.";
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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GalaxyTech</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body class="body-login">
    <article class="login-card">
        <header class="login-header">
            <h2>GalaxyTech</h2>
            <small class="texto-secundario">Ingreso al Sistema de Gestión</small>
        </header>
        
        <?php if ($error !== ""): ?>
            <div class="alerta-error"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <form method="POST" action="">
            <label for="usuario">Usuario
                <input type="text" id="usuario" name="usuario" placeholder="Ingresá tu usuario" required autofocus>
            </label>

            <label for="clave">Contraseña
                <input type="password" id="clave" name="clave" placeholder="••••••••" required>
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Ingresar</button>
        </form>
    </article>
</body>
</html>