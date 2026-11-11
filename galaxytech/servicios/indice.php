<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
require_once '../config/conexion.php';
$conn = getConnection();
$resClientes = $conn->query("SELECT id_cliente, apellido, nombre FROM clientes WHERE activo = 1 ORDER BY apellido ASC");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Servicios - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="admin-container" style="max-width: 950px;">
        <a href="../principal.php" style="text-decoration: none; color: #666; font-size: 14px;">← Volver al Panel</a>
        <h1>Gestión de Servicios (Operación)</h1>

        <form id="form-servicio">
            <input type="hidden" id="id_servicio" name="id_servicio" value="">
            
            <label>Cliente:</label>
            <select id="id_cliente" name="id_cliente" required style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="">Seleccione un cliente...</option>
                <?php while($cli = $resClientes->fetch_assoc()): ?>
                    <option value="<?php echo $cli['id_cliente']; ?>">
                        <?php echo htmlspecialchars($cli['apellido'] . ", " . $cli['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Equipo / Dispositivo:</label>
            <input type="text" id="equipo" name="equipo" placeholder="Ej: Notebook HP / PC Escritorio" required>

            <label>Descripción / Problema:</label>
            <input type="text" id="descripcion" name="descripcion" placeholder="Ej: No enciende / Cambio de disco" required>

            <label>Estado:</label>
            <select id="estado" name="estado" style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="Pendiente">Pendiente</option>
                <option value="En Proceso">En Proceso</option>
                <option value="Finalizado">Finalizado</option>
            </select>

            <button type="submit" id="btn-guardar-servicio">Guardar</button>
            <button type="button" id="btn-limpiar" onclick="limpiarFormularioServicio()">Limpiar</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Listado de Servicios Activos</h3>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Equipo</th>
                    <th>Descripción</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody id="tabla-servicios-body">
                <!-- Carga dinámica vía fetch -->
            </tbody>
        </table>
    </div>

    <script src="../js/servicios.js"></script>
</body>
</html>
<?php $conn->close(); ?>