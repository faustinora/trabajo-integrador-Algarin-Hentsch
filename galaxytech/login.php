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
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        body {
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
        }
        .login-card {
            width: 100%;
            max-width: 420px;
            padding: 2rem;
            border-radius: 10px;
        }
        .alerta-error {
            background-color: #fecaca;
            color: #991b1b;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            margin-bottom: 1.2rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <article class="login-card">
        <header style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="margin-bottom: 0.25rem;">GalaxyTech</h2>
            <small style="color: #94a3b8;">Ingreso al Sistema de Gestión</small>
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

            <button type="submit" style="width: 100%; margin-top: 1rem;">Ingresar</button>
        </form>
    </article>
</body>
</html>