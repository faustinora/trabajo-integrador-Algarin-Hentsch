<?php

session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - Sistema</title>

    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="dashboard-container">
        <header>
            <h1>Panel de Control</h1>
            <p>Bienvenido/a, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>
            <a href="cierreAdmin.php" class="btn-salir">Cerrar Sesión</a>
        </header>

        <hr>

        <nav class="menu-modulos">
            <h2>Módulos del Sistema</h2>
            <ul>
                <li><a href="clientes/lista_cliente.php">Gestión de Clientes</a></li>
                <li><a href="servicios_listado.php">Gestión de Servicios</a></li>
                <li><a href="ventas_registro.php">Registrar Nueva Venta</a></li>
                <li><a href="reportes.php">Reportes y Resumen Mensual</a></li>
            </ul>
        </nav>
    </div>
</body>
</html>