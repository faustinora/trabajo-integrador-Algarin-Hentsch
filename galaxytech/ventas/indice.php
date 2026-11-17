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
    <title>Gestión de Ventas - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        table th:first-child,
        #tabla-ventas-body td:first-child {
            display: none;
        }
    </style>
</head>
<body>
    <div class="admin-container" style="max-width: 950px;">
        <a href="../principal.php" style="text-decoration: none; color: #666; font-size: 14px;">← Volver al Panel</a>
        <h1>Gestión de Ventas</h1>

        <!-- Formulario -->
        <form id="form-venta">
            <label for="id_cliente">Cliente:</label>
            <div style="display: flex; gap: 10px; margin-top: 5px; margin-bottom: 15px;">
                <select id="id_cliente" name="id_cliente" required style="flex: 1; min-width: 0; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                    <option value="">Seleccione un cliente...</option>
                    <?php while($cli = $resClientes->fetch_assoc()): ?>
                        <option value="<?php echo $cli['id_cliente']; ?>">
                            <?php echo htmlspecialchars($cli['apellido'] . ", " . $cli['nombre']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
                <a href="../clientes/indice.php" style="display: inline-flex; align-items: center; padding: 0 12px; border: 1px solid #ccc; border-radius: 4px; color: #444; text-decoration: none; white-space: nowrap;" aria-label="Seleccionar o crear un cliente">+ Cliente</a>
            </div>

            <label>Monto Total ($):</label>
            <input type="number" step="0.01" id="total" name="total" placeholder="0.00" required>

            <button type="submit" id="btn-guardar-venta">Registrar</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Listado de Ventas Registradas</h3>
        <table>
            <thead>
                <tr>
                    <th>Cliente</th>
                    <th>Total</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody id="tabla-ventas-body">
                <!-- Carga dinámica vía fetch -->
            </tbody>
        </table>
    </div>
    <script src="../js/ventas.js"></script>
</body>
</html>
<?php $conn->close(); ?>