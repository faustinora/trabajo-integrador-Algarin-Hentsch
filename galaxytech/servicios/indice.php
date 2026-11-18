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
    <title>Gestión de Servicios - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-ancho">
        <a href="<?php echo $panelVolver; ?>" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Gestión de Servicios Técnicos</h1>
            <a href="nuevo.php" class="btn-nuevo">+ Nuevo Servicio</a>
        </div>

        <div class="buscador-inline-container">
            <input type="text" id="inputBuscador" placeholder="Buscar por equipo, diagnóstico o cliente...">
            
            <select id="selectFiltroEstado" class="filtro-estado-wrapper">
                <option value="todos">Todos los estados</option>
                <option value="pendiente">Pendiente</option>
                <option value="en proceso">En Proceso</option>
                <option value="completado">Completado</option>
                <option value="entregado">Entregado</option>
            </select>

            <button type="button" id="btnBuscar">Buscar</button>
        </div>

        <h3>Listado de Reparaciones</h3>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Cliente</th>
                        <th>Equipo / Diagnóstico</th>
                        <th>Costo</th>
                        <th>Estado</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-servicios-body"></tbody>
            </table>
        </div>
        
        <footer>
            <small id="totalRegistros"><strong>Total:</strong> 0 servicios</small>
        </footer>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/servicios.js"></script>
</body>
</html>