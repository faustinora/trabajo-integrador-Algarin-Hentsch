<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$panelVolver = (($_SESSION['rol'] ?? '') === 'admin') ? '../panelAdmin.php' : '../panelVendedor.php';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal">
        <a href="<?php echo htmlspecialchars($panelVolver, ENT_QUOTES, 'UTF-8'); ?>" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Gestión de Clientes</h1>
            <a href="nuevo.php" class="btn-nuevo">+ Nuevo Cliente</a>
        </div>

        <div class="buscador-inline-container">
            <input type="text" id="inputBuscador" placeholder="Buscar por apellido, nombre, teléfono o email...">

            <select id="selectFiltroEstado" class="filtro-estado-wrapper">
                <option value="1">Activos</option>
                <option value="0">Inactivos</option>
                <option value="todos">Todos</option>
            </select>

            <button type="button" id="btnBuscar">Buscar</button>
        </div>

        <h3>Listado</h3>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Nombre y Apellido</th>
                        <th>Teléfono</th>
                        <th>Email</th>
                        <th>Estado</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-clientes-body"></tbody>
            </table>
        </div>

        <footer>
            <small id="totalRegistros"><strong>Total:</strong> 0 clientes</small>
        </footer>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/clientes.js"></script>
</body>
</html>