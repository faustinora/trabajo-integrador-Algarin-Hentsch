<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Administración - GalaxyTech</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <div class="dashboard-container" style="max-width: 500px;">
        <h2>Panel de Control</h2>
        <p>Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong> (Rol: <?php echo htmlspecialchars($_SESSION['rol']); ?>)</p>
        
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">

        <h3>Menú de Gestión</h3>
        <div class="menu-links">
            <a href="clientes/indice.php">Gestionar Clientes</a>
            <a href="productos/indice.php">Gestionar Productos</a>            
            <a href="servicios/indice.php">Gestionar Servicios (Operación)</a>
            <a href="ventas/indice.php">Gestionar Ventas</a>
            <a href="ventas/detalles.php">Detalle de Ventas</a>
            <a href="informes/resumen.php" style="background-color: #e8f4f8; border-color: #b7e3f4;">Resumen Mensual e Informes (Próximamente)</a>
        </div>

        <a href="cierresesion.php" class="btn btn-salir">Cerrar Sesión</a>
    </div>
</body>
</html>