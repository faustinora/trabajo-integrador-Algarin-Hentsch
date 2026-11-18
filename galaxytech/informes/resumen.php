<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php?error=acceso_no_autorizado");
    exit();
}
$panelVolver = '../panelAdmin.php';
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard e Informes - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-dashboard">
        <a href="<?php echo $panelVolver; ?>" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Dashboard e Indicadores</h1>
        </div>

        
        <div class="tarjeta-inventario">
            <small>Recaudación Total Ventas</small>
            <h3 id="kpi-ventas">$ 0.00</h3>
        </div>

        <div class="kpis-grid">
            <div class="kpi-card">
                <small>Servicios Pendientes</small>
                <h3 id="kpi-pendientes" class="valor-ambar">-</h3>
            </div>
            <div class="kpi-card clickeable" id="card-stock-bajo" title="Clic para ver el listado de productos con stock crítico">
                <small>Productos Stock Bajo</small>
                <h3 id="kpi-stock-bajo" class="valor-rojo">-</h3>
                <span class="texto-ver-detalle">Ver detalle →</span>
            </div>
            <div class="kpi-card">
                <small>Clientes Registrados</small>
                <h3 id="kpi-clientes" class="valor-celeste">-</h3>
            </div>
        </div>

      
        <hr class="separador-seccion">

        <h3 class="titulo-seccion">Resumen de Actividad</h3>
        
        <div class="informes-grid">
            <!-- Informe 1: Productos más vendidos -->
            <div class="informe-box">
                <h4>Productos más vendidos</h4>
                <div class="tabla-contenedor">
                    <table>
                        <thead>
                            <tr>
                                <th>Producto</th>
                                <th class="th-unidades">Unidades</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-inf-productos">
                            <tr><td colspan="2" class="td-centrada">Cargando...</td></tr>
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
                                <th class="th-centrada">Compras</th>
                                <th class="th-centrada">Servicios</th>
                                <th class="th-derecha">Total</th>
                            </tr>
                        </thead>
                        <tbody id="tabla-inf-clientes">
                            <tr><td colspan="4" class="td-centrada">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Modal para ver productos con stock bajo -->
        <dialog id="modal-stock-bajo">
            <article class="modal-articulo">
                <header class="modal-header">
                    <button aria-label="Close" rel="prev" id="cerrar-modal-stock"></button>
                    <h4>Productos con Stock Bajo (≤ 3)</h4>
                </header>
                <table class="tabla-modal">
                    <thead>
                        <tr>
                            <th>Código</th>
                            <th>Producto</th>
                            <th class="th-derecha">Stock</th>
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