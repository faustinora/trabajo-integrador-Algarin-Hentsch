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
    <title>Nuevo Producto - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 600px;">
        <a href="indice.php" class="link-volver">← Volver al Inventario</a>
        <h1>Registrar Nuevo Producto</h1>

        <form id="formNuevoProducto">
            <label for="codigo">Código de Producto
                <input type="text" id="codigo" required placeholder="Ej. PROD-001">
            </label>

            <label for="nombre">Nombre
                <input type="text" id="nombre" required placeholder="Ej. Memoria RAM 8GB">
            </label>

            <label for="descripcion">Descripción
                <textarea id="descripcion" placeholder="Detalles técnicos del producto..."></textarea>
            </label>

            <label for="precio">Precio Unitario ($)
                <input type="number" step="0.01" id="precio" required placeholder="Ej. 25000.00">
            </label>

            <label for="stock">Stock Inicial
                <input type="number" id="stock" min="0" value="1" required>
            </label>

            <button type="submit">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.getElementById("formNuevoProducto").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                codigo: document.getElementById("codigo").value.trim(),
                nombre: document.getElementById("nombre").value.trim(),
                descripcion: document.getElementById("descripcion").value.trim(),
                precio: parseFloat(document.getElementById("precio").value),
                stock: parseInt(document.getElementById("stock").value)
            };

            try {
                const res = await fetch("../api/productos/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message, "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar.", "error");
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