<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}

require_once '../config/conexion.php';
$conn = getConnection();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="main-container">
        <header class="admin-header">
            <h1>Gestión de Clientes</h1>
            <p>Usuario conectado: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>
            <div class="acciones-header">
                <a href="alta_cliente.php" class="btn-primario">Nuevo Cliente</a>
                <a href="../panelAdmin.php" class="btn-secundario">Volver al Panel</a>
            </div>
        </header>

        <hr>

        <section class="seccion-tabla">
            <h2>Listado de Clientes</h2>
            
            <table class="tabla-datos">
                <thead>
                    <tr>
                        <th>Nombre y Apellido</th>
                        <th>Teléfono</th>
                        <th>Dirección</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT id_cliente, nombre, apellido, telefono, direccion FROM clientes WHERE activo = 1";
                    $resultado = $conn->query($sql);

                    if ($resultado && $resultado->num_rows > 0) {
                        while ($row = $resultado->fetch_assoc()) {
                            ?>
                            <tr>
                            <td><?php echo htmlspecialchars($row['nombre'] . " " . $row['apellido']); ?></td>
                            <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                            <td><?php echo htmlspecialchars($row['direccion']); ?></td>
                            <td>
                                    <a href="editar_cliente.php?id=<?php $row['id_cliente']; ?>" class="btn-accion btn-editar">Editar</a>
                                    <a href="#" onclick="confirmarBaja(<?php echo $row['id_cliente']; ?>)">Eliminar</a>
                                  </td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo "<tr><td colspan='5'>No hay registros.</td></tr>";
                    }
                    $conn->close();
                    ?>
                </tbody>
            </table>
        </section>
    </div>
</body>
    <script src="../js/main.js"></script>
</html>