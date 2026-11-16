<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nuevo Cliente - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 600px;">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Registrar Nuevo Cliente</h1>

        <form id="formNuevoCliente">
            <label for="nombre">Nombre
                <input type="text" id="nombre" required placeholder="Ej. Juan">
            </label>

            <label for="apellido">Apellido
                <input type="text" id="apellido" required placeholder="Ej. Pérez">
            </label>

            <label for="telefono">Teléfono
                <input type="text" id="telefono" required placeholder="Ej. 3764123456">
            </label>

            <label for="email">Correo Electrónico
                <input type="email" id="email" placeholder="Ej. juan@email.com">
            </label>

            <button type="submit">Guardar Cliente</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.getElementById("formNuevoCliente").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                nombre: document.getElementById("nombre").value.trim(),
                apellido: document.getElementById("apellido").value.trim(),
                telefono: document.getElementById("telefono").value.trim(),
                email: document.getElementById("email").value.trim()
            };

            try {
                const res = await fetch("../api/clientes/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message, "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar cliente.", "error");
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