<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
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
    <div class="main-container">
        <header class="admin-header">
            <h1>Registrar</h1>
            <p>Usuario conectado: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>
        </header>

        <hr>

        <section class="seccion-formulario">
            <form action="guardar_cliente.php" method="POST" class="form-registro">
                
                <div class="campo-grupo">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" required>
                </div>

                <div class="campo-grupo">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" required>
                </div>

                <div class="campo-grupo">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono">
                </div>

                <div class="campo-grupo">
                    <label for="email">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" required>
                </div>

                <div class="acciones-form">
                    <button type="submit" class="btn-primario">Guardar</button>
                    <a href="lista_cliente.php" class="btn-secundario">Cancelar</a>
                </div>

            </form>
        </section>
    </div>
</body>
</html>