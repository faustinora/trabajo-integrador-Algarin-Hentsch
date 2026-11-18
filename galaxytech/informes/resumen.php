<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php?error=acceso_no_autorizado");
    exit();
}
$panelVolver = '../panelAdmin.php';
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard e Informes - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .filtro-estado-wrapper {
            max-width: 220px;
            margin-bottom: 0;
        }
        .kpi-card {
        padding: 12px 16px !important;
    }
    </style>
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 1100px;">
        <a href="<?php echo $panelVolver; ?>" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Dashboard e Indicadores</h1>
        </div>

        <!-- 4 Indicadores KPIs Principales -->
        <div class="kpis-grid">
            <div class="tarjeta-inventario kpi-card">
                <small>Recaudación Total Ventas</small>
                <h3 id="kpi-ventas" class="valor-verde">$ 0</h3>
            </div>
            <div class="tarjeta-inventario kpi-card">
                <small>Servicios Pendientes</small>
                <h3 id="kpi-pendientes" class="valor-ambar">-</h3>
            </div>
            <div class="tarjeta-inventario kpi-card clickeable" id="card-stock-bajo" style="cursor: pointer;" title="Clic para ver el listado de productos con stock crítico">
                <small>Productos Stock Bajo (≤ 3)</small>
                <h3 id="kpi-stock-bajo" class="valor-rojo">-</h3>
                <span style="font-size: 0.75rem; color: #94a3b8; display: block; margin-top: 4px;">Ver detalle →</span>
            </div>
            <div class="tarjeta-inventario kpi-card">
                <small>Clientes Registrados</small>
                <h3 id="kpi-clientes" class="valor-celeste">-</h3>
            </div>
        </div>

        <hr style="margin: 28px 0; border-color: #334155;">

        <h3 style="margin-bottom: 10px;">Resumen de Actividad</h3>
        
        <div class="informes-grid">
            <!-- Informe 1: Productos más vendidos -->
            <div class="informe-box">
                <h4>Productos más vendidos</h4>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th style="width: 100px; text-align: right;">Unidades</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-inf-productos">
                            <tr><td colspan="2" style="text-align:center;">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Informe 2: Clientes con mayor actividad -->
            <div class="informe-box">
                <h4>Clientes con mayor actividad</h4>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Cliente</th>
                                <th style="text-align: center;">Compras</th>
                                <th style="text-align: center;">Servicios</th>
                                <th style="text-align: right;">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-inf-clientes">
                            <tr><td colspan="4" style="text-align:center;">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para ver productos con stock bajo -->
        <dialog id="modal-stock-bajo">
            <article style="max-width: 520px; width: 100%; background: #1e293b; border: 1px solid #334155;">
                <header style="border-bottom: 1px solid #334155; background: #1e293b;">
                    <button aria-label="Close" rel="prev" id="cerrar-modal-stock"></button>
                    <h4 style="margin: 0; color: #f8fafc;">Productos con Stock Bajo (≤ 3)</h4>
                </header>
                <table style="margin-bottom: 0;">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th style="text-align: right;">Stock</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-modal-stock"></tbody>
                </table>
            </article>
        </dialog>
    </main>

    <div id="toastContainer"></div>

    <script src="../js/informes.js"></script>
</body>
</html>