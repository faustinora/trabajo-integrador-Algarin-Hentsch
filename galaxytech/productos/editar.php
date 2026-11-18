<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$id_producto = (int)($_GET['id'] ?? 0);
if ($id_producto <= 0) {
    header("Location: indice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-estrecho">
        <a href="indice.php" class="link-volver">← Volver al Inventario</a>
        <h1>Editar Producto</h1>

        <form id="formEditarProducto">
            <input type="hidden" id="id_producto" value="<?php echo $id_producto; ?>">

            <label for="codigo">Código de Producto
                <input type="text" id="codigo" required>
            </label>

            <label for="nombre">Nombre
                <input type="text" id="nombre" required>
            </label>

            <label for="descripcion">Descripción
                <textarea id="descripcion"></textarea>
            </label>

            <label for="precio">Precio Unitario ($)
                <input type="number" step="0.01" min="0.01" id="precio" required>
            </label>

            <label for="stock">Stock Actual
                <input type="number" id="stock" min="1" required>
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/productos.js"></script>
</body>
</html>