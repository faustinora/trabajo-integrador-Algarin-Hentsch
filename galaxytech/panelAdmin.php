<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - GalaxyTech</title>
    <link rel="stylesheet" href="css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-panel">
        <article>
            <div class="panel-header">
                <div>
                    <h2>Panel de Administración</h2>
                    <p class="panel-subtitulo">
                        Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong> 
                        <span class="badge-rol">Administrador</span>
                    </p>
                </div>
                <a href="cierresesion.php" class="btn-salir-panel">Cerrar Sesión</a>
            </div>

            <hr class="separador-seccion">

            <h3 class="panel-subtitulo-modulos">Módulos de Gestión</h3>
            <div class="menu-grid">
                <a href="clientes/indice.php" class="btn-menu-secundario">Gestionar Clientes</a>
                <a href="productos/indice.php" class="btn-menu-secundario">Gestionar Productos</a>            
                <a href="servicios/indice.php" class="btn-menu-secundario">Gestionar Servicios</a>
                <a href="ventas/indice.php" class="btn-menu-secundario">Gestionar Ventas</a>
                <a href="informes/resumen.php" class="btn-menu-principal">Dashboard e Informes</a>
                <a href="usuarios/indice.php" class="btn-menu-secundario">Gestionar Usuarios</a>
            </div>
        </article>
    </main>
</body>
</html>