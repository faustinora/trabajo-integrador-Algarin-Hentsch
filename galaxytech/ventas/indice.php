<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$panelVolver = ($_SESSION['rol'] === 'admin') ? '../panelAdmin.php' : '../panelVendedor.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Ventas - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal">
        <a href="<?php echo $panelVolver; ?>" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Gestión de Ventas</h1>
            <a href="nueva.php" role="button">+ Nueva Venta</a>
        </div>

        <div class="tarjeta-inventario">
            <small>Recaudación Total de Ventas</small>
            <h3 id="spanTotalVentas">$0.00</h3>
        </div>

        <div class="buscador-inline-container">
            <input type="text" id="inputBuscador" placeholder="Buscar por cliente, vendedor o fecha...">
            <button type="button" id="btnBuscar">Buscar</button>
        </div>

        <h3>Historial de Ventas</h3>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Vendedor</th>
                        <th>Total</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-ventas-body"></tbody>
            </table>
        </div>
        
        <footer>
            <small id="totalRegistros"><strong>Total:</strong> 0 ventas</small>
        </footer>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/ventas.js"></script>
</body>
</html>