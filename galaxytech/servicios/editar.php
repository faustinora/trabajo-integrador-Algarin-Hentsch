<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$id_servicio = (int)($_GET['id'] ?? 0);
if ($id_servicio <= 0) {
    header("Location: indice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Servicio - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-mediano">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Editar Servicio Técnico</h1>

        <form id="formEditarServicio">
            <input type="hidden" id="id_servicio" value="<?php echo $id_servicio; ?>">

            <label for="equipo_descripcion">Descripción del Equipo / Modelo
                <input type="text" id="equipo_descripcion" required>
            </label>

            <label for="diagnostico">Diagnóstico / Reparación Realizada
                <textarea id="diagnostico"></textarea>
            </label>

            <label for="costo">Costo ($)
                <input type="number" step="0.01" id="costo" required>
            </label>

            <label for="estado">Estado del Servicio
                <select id="estado">
                    <option value="pendiente">Pendiente</option>
                    <option value="en proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                    <option value="entregado">Entregado</option>
                </select>
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>
    <script src="../js/servicios.js"></script>
</body>
</html>