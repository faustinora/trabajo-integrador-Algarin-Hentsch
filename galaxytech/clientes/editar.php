<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$id_cliente = (int)($_GET['id'] ?? 0);
if ($id_cliente <= 0) {
    header("Location: indice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-estrecho">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Editar Cliente</h1>

        <form id="formEditarCliente">
            <input type="hidden" id="id_cliente" value="<?php echo $id_cliente; ?>">

            <label for="nombre">Nombre
                <input type="text" id="nombre" required>
            </label>

            <label for="apellido">Apellido
                <input type="text" id="apellido" required>
            </label>

            <label for="telefono">Teléfono
                <input type="text" id="telefono" required>
            </label>

            <label for="email">Email
                <input type="email" id="email">
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/clientes.js"></script>
</body>
</html>