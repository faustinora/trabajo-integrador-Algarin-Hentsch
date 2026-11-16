<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="css/estilos.css">
    <style>
        .panel-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .badge-rol {
            background-color: #bfdbfe;
            color: #1e40af;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            vertical-align: middle;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
            gap: 1rem;
            margin-top: 1.5rem;
        }
        .menu-grid a[role="button"] {
            padding: 1.25rem 1rem;
            font-weight: 600;
            text-align: center;
            margin: 0;
        }
        .btn-salir-panel {
            background-color: #c0392b !important;
            border-color: #c0392b !important;
            color: #ffffff !important;
        }
    </style>
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 850px; margin-top: 2rem;">
        <article>
            <div class="panel-header">
                <div>
                    <h2 style="margin-bottom: 0.25rem;">Panel de Administración</h2>
                    <p style="margin: 0; color: #94a3b8;">
                        Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong> 
                        <span class="badge-rol">Administrador</span>
                    </p>
                </div>
                <a href="cierresesion.php" role="button" class="btn-salir-panel">Cerrar Sesión</a>
            </div>

            <hr>

            <h3 style="font-size: 1.15rem; color: #cbd5e1;">Módulos de Gestión</h3>
            <div class="menu-grid">
                <a href="clientes/indice.php" role="button" class="secondary">Gestionar Clientes</a>
                <a href="productos/indice.php" role="button" class="secondary">Gestionar Productos</a>            
                <a href="servicios/indice.php" role="button" class="secondary">Gestionar Servicios</a>
                <a href="ventas/indice.php" role="button" class="secondary">Gestionar Ventas</a>
                <a href="informes/resumen.php" role="button">Dashboard e Informes</a>
                <a href="usuarios/indice.php" role="button" class="secondary">Gestionar Usuarios</a>
            </div>
        </article>
    </main>
</body>
</html>