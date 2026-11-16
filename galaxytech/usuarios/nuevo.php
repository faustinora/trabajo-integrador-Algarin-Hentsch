<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php?error=acceso_no_autorizado");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nuevo Usuario - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 600px;">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Registrar Nuevo Usuario</h1>

        <form id="formNuevoUsuario">
            <label for="usuario">Nombre de Usuario
                <input type="text" id="usuario" required placeholder="Ej. jperez" autofocus>
            </label>

            <label for="clave">Contraseña
                <input type="password" id="clave" required placeholder="Mínimo 4 caracteres">
            </label>

            <label for="rol">Rol del Sistema
                <select id="rol" required>
                    <option value="vendedor" selected>vendedor</option>
                    <option value="admin">admin</option>
                </select>
            </label>

            <button type="submit" style="margin-top: 1rem;">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.getElementById("formNuevoUsuario").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                usuario: document.getElementById("usuario").value.trim(),
                clave: document.getElementById("clave").value.trim(),
                rol: document.getElementById("rol").value
            };

            try {
                const res = await fetch("../api/usuarios/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && (resultado.success || resultado.status === "success")) {
                    mostrarToast(resultado.message || "Usuario registrado con éxito.", "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar usuario.", "error");
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