<?php
session_start();

// Si no hay sesión o no es vendedor (ni admin), lo sacamos
if (!isset($_SESSION['usuario']) || ($_SESSION['rol'] !== 'vendedor' && $_SESSION['rol'] !== 'admin')) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Vendedor - GalaxyTech</title>
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
        .badge-rol-vendedor {
            background-color: #bbf7d0;
            color: #166534;
            padding: 3px 10px;
            border-radius: 4px;
            font-size: 0.8rem;
            font-weight: bold;
            vertical-align: middle;
        }
        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
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
    <main class="container contenedor-principal" style="max-width: 800px; margin-top: 2rem;">
        <article>
            <div class="panel-header">
                <div>
                    <h2 style="margin-bottom: 0.25rem;">Panel de Ventas</h2>
                    <p style="margin: 0; color: #94a3b8;">
                        Bienvenido, <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong> 
                        <span class="badge-rol-vendedor">Vendedor</span>
                    </p>
                </div>
                <a href="cierresesion.php" role="button" class="btn-salir-panel">Cerrar Sesión</a>
            </div>

            <hr>

            <h3 style="font-size: 1.15rem; color: #cbd5e1;">Operaciones Habilitadas</h3>
            <div class="menu-grid">
                <a href="clientes/indice.php" role="button" class="secondary">Clientes (Consultar / Alta)</a>
                <a href="servicios/indice.php" role="button" class="secondary">Registrar / Consultar Servicios</a>
                <a href="ventas/indice.php" role="button">Registrar Nueva Venta</a>
            </div>
        </article>
    </main>
</body>
</html>