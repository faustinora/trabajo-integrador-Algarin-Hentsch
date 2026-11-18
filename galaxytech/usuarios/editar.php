<?php
session_start();
if (!isset($_SESSION['usuario']) || $_SESSION['rol'] !== 'admin') {
    header("Location: ../login.php?error=acceso_no_autorizado");
    exit();
}
$id_usuario = (int)($_GET['id'] ?? 0);
if ($id_usuario <= 0) {
    header("Location: indice.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es" data-theme="dark">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Usuario - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 600px;">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Editar Usuario</h1>

        <form id="formEditarUsuario">
            <input type="hidden" id="id_usuario" value="<?php echo $id_usuario; ?>">

            <label for="usuario">Nombre de Usuario
                <input type="text" id="usuario" required>
            </label>

            <label for="clave">Nueva Contraseña <small style="color: #94a3b8;">(Dejar en blanco para mantener la actual)</small>
                <input type="password" id="clave" placeholder="••••••••">
            </label>

            <label for="rol">Rol del Sistema
                <select id="rol" required>
                    <option value="vendedor">Vendedor</option>
                    <option value="admin">Administrador</option>
                </select>
            </label>

            <label for="activo">Estado
                <select id="activo" required>
                    <option value="1">Activo</option>
                    <option value="0">Inactivo</option>
                </select>
            </label>

            <button type="submit" style="margin-top: 1rem;">Guardar Cambios</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const id = document.getElementById("id_usuario").value;
            try {
                const res = await fetch("../api/usuarios/listar.php?status=todos");
                const data = await res.json();
                const usuarios = Array.isArray(data) ? data : (data.usuarios || data.data || []);
                const user = usuarios.find(u => u.id_usuario == id);

                if (user) {
                    document.getElementById("usuario").value = user.usuario;
                    document.getElementById("rol").value = user.rol;
                    document.getElementById("activo").value = user.activo;
                } else {
                    mostrarToast("No se encontró el usuario.", "error");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                }
            } catch (err) {
                mostrarToast("Error al cargar los datos del usuario.", "error");
            }
        });

        document.getElementById("formEditarUsuario").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_usuario: document.getElementById("id_usuario").value,
                usuario: document.getElementById("usuario").value.trim(),
                clave: document.getElementById("clave").value.trim(),
                rol: document.getElementById("rol").value,
                activo: parseInt(document.getElementById("activo").value)
            };

            try {
                const res = await fetch("../api/usuarios/actualizar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && (resultado.success || resultado.status === "success")) {
                    mostrarToast(resultado.message || "Usuario actualizado correctamente.", "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al actualizar usuario.", "error");
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