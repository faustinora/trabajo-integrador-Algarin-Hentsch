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
    <title>Nuevo Producto - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-estrecho">
        <a href="indice.php" class="link-volver">← Volver al Inventario</a>
        <h1>Registrar Nuevo Producto</h1>

        <form id="formNuevoProducto">
            <label for="codigo">Código de Producto
                <input type="text" id="codigo" required placeholder="Ej. PROD-001">
            </label>

            <label for="nombre">Nombre
                <input type="text" id="nombre" required placeholder="Ej. Memoria RAM 8GB">
            </label>

            <label for="descripcion">Descripción
                <textarea id="descripcion" placeholder="Detalles técnicos del producto..."></textarea>
            </label>

            <label for="precio">Precio Unitario ($)
                <input type="number" step="0.01" min="0.01" id="precio" required placeholder="Ej. 25000.00">
            </label>

            <label for="stock">Stock Inicial
                <input type="number" id="stock" min="1" value="1" required>
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/productos.js"></script>
</body>
</html>