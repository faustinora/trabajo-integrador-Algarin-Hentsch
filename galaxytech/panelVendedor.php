<?php
session_start();

if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Vendedor - GalaxyTech</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-panel">
        <article>
            <div class="panel-header">
                <div>
                    <h2>Panel de Ventas</h2>
                    <p class="panel-subtitulo">
                        Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong> 
                        <span class="badge-rol-vendedor">Vendedor</span>
                    </p>
                </div>
                <a href="cierresesion.php" class="btn-salir-panel">Cerrar Sesión</a>
            </div>

            <hr class="separador-seccion">

            <h3 class="panel-subtitulo-modulos">Operaciones Habilitadas</h3>
            <div class="menu-grid">
                <a href="clientes/indice.php" class="btn-menu-secundario">Clientes (Consultar / Alta)</a>
                <a href="servicios/indice.php" class="btn-menu-secundario">Registrar / Consultar Servicios</a>
                <a href="ventas/indice.php" class="btn-menu-principal">Registrar Nueva Venta</a>
            </div>
        </article>
    </main>
</body>
</html>