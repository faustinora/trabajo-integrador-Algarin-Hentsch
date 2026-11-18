<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
$id_producto = (int)($_GET['id'] ?? 0);
if ($id_producto <= 0) {
    header("Location: indice.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Producto - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 600px;">
        <a href="indice.php" class="link-volver">← Volver al Inventario</a>
        <h1>Editar Producto</h1>

        <form id="formEditarProducto">
            <input type="hidden" id="id_producto" value="<?php echo $id_producto; ?>">

            <label for="codigo">Código de Producto
                <input type="text" id="codigo" required>
            </label>

            <label for="nombre">Nombre
                <input type="text" id="nombre" required>
            </label>

            <label for="descripcion">Descripción
                <textarea id="descripcion"></textarea>
            </label>

            <label for="precio">Precio Unitario ($)
                <input type="number" step="0.01" id="precio" required>
            </label>

            <label for="stock">Stock Actual
                <input type="number" id="stock" min="0" required>
            </label>

            <button type="submit">Guardar</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        document.addEventListener("DOMContentLoaded", async () => {
            const id = document.getElementById("id_producto").value;
            try {
                const res = await fetch("../api/productos/listar.php?status=todos");
                const productos = await res.json();
                const producto = productos.find(p => p.id_producto == id);

                if (producto) {
                    document.getElementById("codigo").value = producto.codigo;
                    document.getElementById("nombre").value = producto.nombre;
                    document.getElementById("descripcion").value = producto.descripcion || '';
                    document.getElementById("precio").value = producto.precio;
                    document.getElementById("stock").value = producto.stock;
                } else {
                    mostrarToast("No se encontró el producto.", "error");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                }
            } catch (err) {
                mostrarToast("Error al cargar los datos del producto.", "error");
            }
        });

        document.getElementById("formEditarProducto").addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_producto: document.getElementById("id_producto").value,
                codigo: document.getElementById("codigo").value.trim(),
                nombre: document.getElementById("nombre").value.trim(),
                descripcion: document.getElementById("descripcion").value.trim(),
                precio: parseFloat(document.getElementById("precio").value),
                stock: parseInt(document.getElementById("stock").value)
            };

            try {
                const res = await fetch("../api/productos/actualizar.php", {
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