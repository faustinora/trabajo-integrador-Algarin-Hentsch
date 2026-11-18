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
    <title>Gestión de Productos - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal">
        <a href="<?php echo $panelVolver; ?>" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Gestión de Productos</h1>
            <a href="nuevo.php" class="btn-nuevo">+ Nuevo Producto</a>
        </div>

        <div class="tarjeta-inventario">
            <small>Valor Total del Inventario</small>
            <h3 id="spanValorInventario">$0.00</h3>
        </div>

        <div class="buscador-inline-container">
            <input type="text" id="inputBuscador" placeholder="Buscar por código, nombre o descripción...">
            
            <select id="selectFiltroEstado" class="filtro-estado-wrapper">
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
                <option value="todos">Todos</option>
            </select>

            <button type="button" id="btnBuscar">Buscar</button>
        </div>

        <h3>Listado de Stock</h3>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Código</th>
                        <th>Nombre</th>
                        <th>Precio</th>
                        <th>Stock</th>
                        <th>Estado</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-productos-body"></tbody>
            </table>
        </div>
        
        <footer>
            <small id="totalRegistros"><strong>Total:</strong> 0 productos</small>
        </footer>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/productos.js"></script>
</body>
</html>