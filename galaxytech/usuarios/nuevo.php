<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php?error=acceso_no_autorizado");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-estrecho">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Registrar Nuevo</h1>

        <form id="formNuevoUsuario">
            <label for="usuario">Nombre
                <input type="text" id="usuario" required placeholder="Ej. jperez" autofocus>
            </label>

            <label for="clave">Contraseña
                <input type="password" id="clave" required placeholder="Mínimo 4 caracteres">
            </label>

            <label for="rol">Rol del Sistema
                <select id="rol" required>
                    <option value="vendedor" selected>vendedor</option>
                    <option value="admin">admin</option>
                </select>
            </label>

            <button type="submit" class="btn-principal">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/usuarios.js"></script>
</body>
</html>