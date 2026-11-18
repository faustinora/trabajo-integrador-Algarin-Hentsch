<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal">
        <a href="../panelAdmin.php" class="link-volver">← Volver al Panel</a>

        <div class="header-gestion">
            <h1>Gestión de Usuarios</h1>
            <a href="nuevo.php" class="btn-nuevo">+ Nuevo Usuario</a>
        </div>

        <div class="buscador-inline-container buscador-usuarios">
            <input type="text" id="inputBuscador" placeholder="Buscar por nombre de usuario o rol...">
    
            <select id="filtroEstado" class="filtro-estado-usuarios">
                <option value="1" selected>Activos</option>
                <option value="todos">Todos</option>
                <option value="0">Inactivos</option>
            </select>

            <button type="button" id="btnBuscar">Buscar</button>
        </div>

        <h3>Listado</h3>
        <div class="tabla-contenedor">
            <table>
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Estado</th>
                        <th class="col-acciones">Acciones</th>
                    </tr>
                </thead>
                <tbody id="tabla-usuarios-body"></tbody>
            </table>
        </div>
        
        <footer>
            <small id="totalRegistros"><strong>Total:</strong> 0 usuarios</small>
        </footer>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/usuarios.js"></script>
</body>
</html>