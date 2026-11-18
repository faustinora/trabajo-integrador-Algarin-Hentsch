<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php?error=acceso_no_autorizado");
    exit();
}
$id_usuario = (int)($_GET['id'] ?? 0);
if ($id_usuario <= 0) {
    header("Location: indice.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-estrecho">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Editar Usuario</h1>

        <form id="formEditarUsuario">
            <input type="hidden" id="id_usuario" value="<?php echo $id_usuario; ?>">

            <label for="usuario">Nombre de Usuario
                <input type="text" id="usuario" required>
            </label>

            <label for="clave">Nueva Contraseña <small class="texto-secundario">(Dejar en blanco para mantener la actual)</small>
                <input type="password" id="clave" placeholder="••••••••">
            </label>

            <label for="rol">Rol del Sistema
                <select id="rol" required>
                    <option value="vendedor">vendedor</option>
                    <option value="admin">admin</option>
                </select>
            </label>

            <label for="activo">Estado
                <select id="activo" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </label>

            <button type="submit" class="btn-principal">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/usuarios.js"></script>
</body>
</html>