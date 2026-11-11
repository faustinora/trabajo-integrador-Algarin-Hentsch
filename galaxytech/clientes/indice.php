<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="admin-container">
        <a href="../principal.php" style="text-decoration: none; color: #666; font-size: 14px;">← Volver a Principal</a>
        <h1>Gestión de Clientes</h1>

        <form id="form-cliente">
            <input type="hidden" id="id_cliente" name="id_cliente" value="">
            
            <label>Nombre:</label>
            <input type="text" id="nombre" name="nombre" required>

            <label>Apellido:</label>
            <input type="text" id="apellido" name="apellido" required>
            
            <label>Teléfono:</label>
            <input type="text" id="telefono" name="telefono" required>

            <label>Email:</label>
            <input type="email" id="email" name="email" required>

            <button type="submit" id="btn-guardar">Guardar</button>
            <button type="button" id="btn-limpiar" onclick="limpiarFormulario()">Limpiar</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Listado de Clientes</h3>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Teléfono</th>
                    <th>Email</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-clientes-body">
                <!-- Carga dinámicamente vía fetch -->
            </tbody>
        </table>
    </div>

    <script src="../js/clientes.js"></script>
</body>
</html>