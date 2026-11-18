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
    <title>Nuevo Cliente - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-estrecho">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Registrar Nuevo Cliente</h1>

        <form id="formNuevoCliente">
            <label for="nombre">Nombre
                <input type="text" id="nombre" required placeholder="Ej. Juan">
            </label>

            <label for="apellido">Apellido
                <input type="text" id="apellido" required placeholder="Ej. Pérez">
            </label>

            <label for="telefono">Teléfono
                <input type="text" id="telefono" required placeholder="Ej. 3764123456">
            </label>

            <label for="email">Email
                <input type="email" id="email" placeholder="Ej. juan@email.com">
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/clientes.js"></script>
</body>
</html>