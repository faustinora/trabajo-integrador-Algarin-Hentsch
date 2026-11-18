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
    <title>Nuevo Servicio - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .select-cliente-wrapper {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .select-cliente-wrapper select {
            flex: 1;
            margin-bottom: 0;
        }
    </style>
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 650px;">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Registrar Nuevo Servicio Técnico</h1>

        <form id="formNuevoServicio">
            <label for="selectCliente">Cliente</label>
            <div class="select-cliente-wrapper">
                <select id="selectCliente" required>
                    <option value="" disabled selected>Cargando clientes...</option>
                </select>
                <button type="button" id="btnNuevoClienteModal" class="secondary" style="margin-bottom: 0; width: auto; white-space: nowrap;">+ Nuevo</button>
            </div>

            <label for="equipo_descripcion" style="margin-top: 15px;">Descripción del Equipo / Modelo
                <input type="text" id="equipo_descripcion" required placeholder="Ej. Notebook HP Pavilion 15 - No enciende">
            </label>

            <label for="diagnostico">Diagnóstico / Falla Reportada
                <textarea id="diagnostico" placeholder="Detalles de la revisión o problema manifestado..."></textarea>
            </label>

            <label for="costo">Costo Estimado ($)
                <input type="number" step="0.01" id="costo" required value="0.00">
            </label>

            <label for="estado">Estado Inicial
                <select id="estado">
                    <option value="pendiente" selected>Pendiente</option>
                    <option value="en proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                </select>
            </label>

            <button type="submit" style="margin-top: 20px;">Guardar Servicio</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            await cargarSelectClientes();

            document.getElementById("btnNuevoClienteModal").addEventListener("click", async () => {
                const apellido = prompt("Ingrese el Apellido del nuevo cliente:");
                if (!apellido) return;
                const nombre = prompt("Ingrese el Nombre del cliente:");
                if (!nombre) return;
                const telefono = prompt("Ingrese el Teléfono:") || "";
                const email = prompt("Ingrese el Email (opcional):") || "";

                try {
                    const res = await fetch("../api/clientes/guardar.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ nombre, apellido, telefono, email })
                    });
                    const data = await res.json();
                    if (res.ok && data.success) {
                        mostrarToast("Cliente registrado.", "exito");
                        await cargarSelectClientes();
                    } else {
                        mostrarToast(data.message || "No se pudo registrar.", "error");
                    }
                } catch (err) {
                    mostrarToast("Error de conexión al registrar cliente.", "error");
                }
            });
        });

        async function cargarSelectClientes() {
            try {
                const res = await fetch("../api/clientes/listar.php");
                const clientes = await res.json();
                const select = document.getElementById("selectCliente");
                select.innerHTML = '<option value="" disabled selected>-- Seleccione un cliente --</option>';
                clientes.forEach(c => {
                    select.innerHTML += `<option value="${c.id_cliente}">${c.nombre}, ${c.apellido} (${c.telefono})</option>`;
                });
            } catch (err) {
                mostrarToast("Error al cargar clientes", "error");
            }
        }

        document.getElementById("formNuevoServicio").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_cliente: document.getElementById("selectCliente").value,
                equipo_descripcion: document.getElementById("equipo_descripcion").value.trim(),
                diagnostico: document.getElementById("diagnostico").value.trim(),
                costo: parseFloat(document.getElementById("costo").value) || 0,
                estado: document.getElementById("estado").value
            };

            try {
                const res = await fetch("../api/servicios/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message, "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar el servicio.", "error");
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