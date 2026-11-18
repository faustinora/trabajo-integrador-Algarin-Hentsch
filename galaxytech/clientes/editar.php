<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$id_cliente = (int)($_GET['id'] ?? 0);
if ($id_cliente <= 0) {
    header("Location: indice.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Cliente - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 600px;">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Editar Cliente</h1>

        <form id="formEditarCliente">
            <input type="hidden" id="id_cliente" value="<?php echo $id_cliente; ?>">

            <label for="nombre">Nombre
                <input type="text" id="nombre" required>
            </label>

            <label for="apellido">Apellido
                <input type="text" id="apellido" required>
            </label>

            <label for="telefono">Teléfono
                <input type="text" id="telefono" required>
            </label>

            <label for="email">Email
                <input type="email" id="email">
            </label>

            <button type="submit">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const id = document.getElementById("id_cliente").value;
            try {
                const res = await fetch(`../api/clientes/listar.php?status=todos`);
                const clientes = await res.json();
                const cliente = clientes.find(c => c.id_cliente == id);

                if (cliente) {
                    document.getElementById("nombre").value = cliente.nombre;
                    document.getElementById("apellido").value = cliente.apellido;
                    document.getElementById("telefono").value = cliente.telefono;
                    document.getElementById("email").value = cliente.email || '';
                } else {
                    mostrarToast("No se encontró el cliente.", "error");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                }
            } catch (err) {
                mostrarToast("Error al cargar los datos del cliente.", "error");
            }
        });

        document.getElementById("formEditarCliente").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_cliente: document.getElementById("id_cliente").value,
                nombre: document.getElementById("nombre").value.trim(),
                apellido: document.getElementById("apellido").value.trim(),
                telefono: document.getElementById("telefono").value.trim(),
                email: document.getElementById("email").value.trim()
            };

            try {
                const res = await fetch("../api/clientes/actualizar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message, "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al actualizar.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });

        function mostrarToast(mensaje, tipo = "exito") {
            const container = document.getElementById("toastContainer");
            if (!container) return;
            const toast = document.createElement("div");
            toast.className = `toast-notificacion ${tipo}`;
            toast.textContent = mensaje;
            container.appendChild(toast);
            setTimeout(() => toast.classList.add("visible"), 10);
            setTimeout(() => {
                toast.classList.remove("visible");
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }
    </script>
</body>
</html>