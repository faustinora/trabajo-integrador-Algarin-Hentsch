<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?error=acceso_no_autorizado");
    exit();
}

require_once '../config/conexion.php';

if (!isset($_GET['id'])) {
    header("Location: lista_cliente.php");
    exit();
}

$id_cliente = intval($_GET['id']);
$conn = getConnection();

$sql = "SELECT id_cliente, nombre, apellido, telefono, direccion FROM clientes WHERE id_cliente = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_cliente);
$stmt->execute();
$resultado = $stmt->get_result();

if ($resultado->num_rows !== 1) {
    header("Location: lista_cliente.php?error=cliente_no_encontrado");
    exit();
}

$cliente = $resultado->fetch_assoc();
$stmt->close();
$conn->close();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <div class="main-container">
        <header class="admin-header">
            <h1>Editar</h1>
            <p>Usuario conectado: <strong><?php echo htmlspecialchars($_SESSION['usuario']); ?></strong></p>
        </header>

        <hr>

        <section class="seccion-formulario">
            <form action="actualizar_cliente.php" method="POST" class="form-registro">
                
                <input type="hidden" name="id_cliente" value="<?php echo $cliente['id_cliente']; ?>">

                <div class="campo-grupo">
                    <label for="nombre">Nombre:</label>
                    <input type="text" id="nombre" name="nombre" value="<?php echo htmlspecialchars($cliente['nombre']); ?>" required>
                </div>

                <div class="campo-grupo">
                    <label for="apellido">Apellido:</label>
                    <input type="text" id="apellido" name="apellido" value="<?php echo htmlspecialchars($cliente['apellido']); ?>" required>
                </div>

                <div class="campo-grupo">
                    <label for="telefono">Teléfono:</label>
                    <input type="text" id="telefono" name="telefono" value="<?php echo htmlspecialchars($cliente['telefono']); ?>">
                </div>

                <div class="campo-grupo">
                    <label for="email">Dirección:</label>
                    <input type="text" id="direccion" name="direccion" value="<?php echo htmlspecialchars($cliente['direccion']); ?>" required>
                </div>

                <div class="acciones-form">
                    <button type="submit" class="btn-primario">Actualizar</button>
                    <a href="lista_cliente.php" class="btn-secundario">Cancelar</a>
                </div>

            </form>
        </section>
    </div>
</body>
</html>