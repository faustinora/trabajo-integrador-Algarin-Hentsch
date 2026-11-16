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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 650px;">
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

            <button type="submit" style="margin-top: 20px;">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const id = document.getElementById("id_servicio").value;
            try {
                const res = await fetch("../api/servicios/listar.php?status=todos");
                const servicios = await res.json();
                const servicio = servicios.find(s => s.id_servicio == id);

                if (servicio) {
                    document.getElementById("equipo_descripcion").value = servicio.equipo_descripcion;
                    document.getElementById("diagnostico").value = servicio.diagnostico || '';
                    document.getElementById("costo").value = servicio.costo;
                    document.getElementById("estado").value = servicio.estado;
                } else {
                    mostrarToast("No se encontró el servicio.", "error");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                }
            } catch (err) {
                mostrarToast("Error al cargar los datos del servicio.", "error");
            }
        });

        document.getElementById("formEditarServicio").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_servicio: document.getElementById("id_servicio").value,
                equipo_descripcion: document.getElementById("equipo_descripcion").value.trim(),
                diagnostico: document.getElementById("diagnostico").value.trim(),
                costo: parseFloat(document.getElementById("costo").value) || 0,
                estado: document.getElementById("estado").value
            };

            try {
                const res = await fetch("../api/servicios/actualizar.php", {
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