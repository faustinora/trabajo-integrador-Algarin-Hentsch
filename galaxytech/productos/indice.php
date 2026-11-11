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
    <title>Gestión de Productos - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="admin-container" style="max-width: 950px;">
        <a href="../principal.php" style="text-decoration: none; color: #666; font-size: 14px;">← Volver al Panel</a>
        <h1>Gestión de Productos / Artículos</h1>

        <form id="form-producto">
            <input type="hidden" id="id_producto" name="id_producto" value="">
            
            <label>Código:</label>
            <input type="text" id="codigo" name="codigo" placeholder="Ej: PROD-001" required>

            <label>Descripción:</label>
            <input type="text" id="descripcion" name="descripcion" placeholder="Ej: Memoria RAM 8GB / Pantalla Repuesto" required>

            <label>Precio ($):</label>
            <input type="number" step="0.01" id="precio" name="precio" placeholder="0.00" required>

            <label>Stock:</label>
            <input type="number" id="stock" name="stock" placeholder="0" required>

            <button type="submit" id="btn-guardar-producto">Guardar</button>
            <button type="button" id="btn-limpiar" onclick="limpiarFormularioProducto()">Limpiar</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Listado de Productos</h3>
        <table>
            <thead>
                <tr>
                    <th>Código</th>
                    <th>Descripción</th>
                    <th>Precio</th>
                    <th>Stock</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-productos-body">
                <!-- Carga dinámica vía fetch -->
            </tbody>
        </table>
    </div>

    <script src="../js/productos.js"></script>
</body>
</html>