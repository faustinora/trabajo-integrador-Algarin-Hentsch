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
</head>
<body>
    <div class="admin-container" style="max-width: 950px;">
        <a href="../principal.php" style="text-decoration: none; color: #666; font-size: 14px;">← Volver al Panel</a>
        <h1>Gestión de Ventas</h1>

        <!-- Formulario -->
        <form id="form-venta">
            <label>Cliente:</label>
            <select id="id_cliente" name="id_cliente" required style="width: 100%; padding: 10px; margin-top: 5px; margin-bottom: 15px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box;">
                <option value="">Seleccione un cliente...</option>
                <?php while($cli = $resClientes->fetch_assoc()): ?>
                    <option value="<?php echo $cli['id_cliente']; ?>">
                        <?php echo htmlspecialchars($cli['apellido'] . ", " . $cli['nombre']); ?>
                    </option>
                <?php endwhile; ?>
            </select>

            <label>Monto Total ($):</label>
            <input type="number" step="0.01" id="total" name="total" placeholder="0.00" required>

            <button type="submit" id="btn-guardar-venta">Registrar</button>
        </form>

        <hr style="margin: 30px 0; border: 0; border-top: 1px solid #eee;">

        <h3>Listado de Ventas Registradas</h3>
        <table>
            <thead>
                <tr>
                    <th>Nro. Venta</th>
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