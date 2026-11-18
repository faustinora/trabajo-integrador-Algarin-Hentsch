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
    <title>Nueva Venta - GalaxyTech</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@picocss/pico@2/css/pico.min.css">
    <link rel="stylesheet" href="../css/estilos.css">
    <style>
        .fila-carrito td { vertical-align: middle; }
        .btn-accion-tabla {
            background-color: #c0392b !important;
            border-color: #c0392b !important;
            color: white;
            padding: 4px 8px;
            font-size: 12px;
            margin: 0;
        }
        .select-cliente-wrapper {
            display: flex;
            gap: 10px;
            align-items: flex-end;
        }
        .select-cliente-wrapper select { flex: 1; margin-bottom: 0; }
    </style>
</head>
<body>
    <main class="container contenedor-principal" style="max-width: 900px;">
        <a href="indice.php" class="link-volver">← Volver al Historial de Ventas</a>
        <h1>Registrar Nueva Venta</h1>

        <form id="formNuevaVenta">
            <label for="selectCliente">Cliente</label>
            <div class="select-cliente-wrapper">
                <select id="selectCliente" required>
                    <option value="" disabled selected>Cargando clientes...</option>
                </select>
                <button type="button" id="btnNuevoClienteModal" class="secondary" style="margin-bottom: 0; width: auto; white-space: nowrap;">+ Nuevo Cliente</button>
            </div>

            <hr style="margin: 25px 0;">

            <h3>Productos en la Venta</h3>
            
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th style="width: 120px;">Precio Unit.</th>
                            <th style="width: 100px;">Cantidad</th>
                            <th style="width: 120px;">Subtotal</th>
                            <th style="width: 80px; text-align: center;">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCarritoBody"></tbody>
                </table>
            </div>

            <div style="margin-bottom: 25px;">
                <button type="button" id="btnAddProducto" class="secondary" style="width: auto;">+ Agregar Producto</button>
            </div>

            <div class="tarjeta-inventario">
                <small>Total a Cobrar</small>
                <h3 id="lblTotalVenta">$0.00</h3>
            </div>

            <button type="submit">Confirmar y Registrar Venta</button>
        </form>
    </main>

    <div id="toastContainer"></div>

    <script>
        let listaProductosGlobal = [];

        document.addEventListener("DOMContentLoaded", async () => {
            await cargarSelectClientes();
            await cargarProductosGlobal();
            agregarFilaCarrito(); // Inicia con una fila por defecto

            document.getElementById("btnAddProducto").addEventListener("click", () => {
                agregarFilaCarrito();
            });

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
                        mostrarToast("Cliente registrado con éxito.", "exito");
                        await cargarSelectClientes();
                    } else {
                        mostrarToast(data.message || "No se pudo registrar el cliente.", "error");
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
                    select.innerHTML += `<option value="${c.id_cliente}">${c.nombre} ${c.apellido} (Tel: ${c.telefono})</option>`;
                });
            } catch (err) {
                mostrarToast("Error al cargar clientes", "error");
            }
        }

        async function cargarProductosGlobal() {
            try {
                const res = await fetch("../api/productos/listar.php");
                const data = await res.json();
                listaProductosGlobal = Array.isArray(data) ? data : (data.productos || data.data || []);
            } catch (err) {
                mostrarToast("Error al cargar productos", "error");
            }
        }

        function agregarFilaCarrito() {
            const tbody = document.getElementById("tablaCarritoBody");
            const tr = document.createElement("tr");
            tr.className = "fila-carrito";

            let optionsHtml = '<option value="" disabled selected>-- Seleccione producto --</option>';
            listaProductosGlobal.forEach(p => {
                if (parseInt(p.stock) > 0) {
                    optionsHtml += `<option value="${p.id_producto}" data-precio="${p.precio}" data-stock="${p.stock}">${p.nombre} (Stock: ${p.stock})</option>`;
                }
            });

            tr.innerHTML = `
                <td><select class="prod-select" required style="margin-bottom:0;">${optionsHtml}</select></td>
                <td class="lbl-precio">$0.00</td>
                <td><input type="number" class="prod-cant" min="1" value="1" required style="margin-bottom:0;"></td>
                <td class="lbl-subtotal">$0.00</td>
                <td style="text-align: center;">
                    <button type="button" class="btn-accion-tabla" onclick="this.closest('tr').remove(); calcularTotalGeneral();">Eliminar</button>
                </td>
            `;

            const selectProd = tr.querySelector(".prod-select");
            const inputCant = tr.querySelector(".prod-cant");
            const lblPrecio = tr.querySelector(".lbl-precio");

            selectProd.addEventListener("change", (e) => {
                const opt = e.target.options[e.target.selectedIndex];
                const precio = parseFloat(opt.dataset.precio) || 0;
                lblPrecio.textContent = `$${precio.toFixed(2)}`;
                actualizarFilaSubtotal(tr);
            });

            inputCant.addEventListener("input", () => {
                actualizarFilaSubtotal(tr);
            });

            tbody.appendChild(tr);
        }

        function actualizarFilaSubtotal(tr) {
            const select = tr.querySelector(".prod-select");
            const cantInput = tr.querySelector(".prod-cant");
            const lblSubtotal = tr.querySelector(".lbl-subtotal");

            const opt = select.options[select.selectedIndex];
            const precio = opt && opt.dataset.precio ? parseFloat(opt.dataset.precio) : 0;
            const stockMax = opt && opt.dataset.stock ? parseInt(opt.dataset.stock) : 9999;
            let cantidad = parseInt(cantInput.value) || 0;

            if (cantidad > stockMax) {
                alert(`Stock máximo disponible: ${stockMax}`);
                cantidad = stockMax;
                cantInput.value = stockMax;
            }

            const subtotal = precio * cantidad;
            lblSubtotal.textContent = `$${subtotal.toFixed(2)}`;
            calcularTotalGeneral();
        }

        function calcularTotalGeneral() {
            let totalGeneral = 0;
            document.querySelectorAll(".fila-carrito").forEach(tr => {
                const select = tr.querySelector(".prod-select");
                const cantInput = tr.querySelector(".prod-cant");
                const opt = select.options[select.selectedIndex];
                if (opt && opt.dataset.precio) {
                    totalGeneral += (parseFloat(opt.dataset.precio) || 0) * (parseInt(cantInput.value) || 0);
                }
            });
            document.getElementById("lblTotalVenta").textContent = `$${totalGeneral.toFixed(2)}`;
        }

        document.getElementById("formNuevaVenta").addEventListener("submit", async (e) => {
            e.preventDefault();

            const id_cliente = document.getElementById("selectCliente").value;
            const filas = document.querySelectorAll(".fila-carrito");
            const productos = [];

            filas.forEach(tr => {
                const id_producto = tr.querySelector(".prod-select").value;
                const cantidad = parseInt(tr.querySelector(".prod-cant").value);
                if (id_producto && cantidad > 0) {
                    productos.push({ id_producto, cantidad });
                }
            });

            if (productos.length === 0) {
                mostrarToast("Debe agregar al menos un producto a la venta.", "error");
                return;
            }

            try {
                const res = await fetch("../api/ventas/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id_cliente, productos })
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message, "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar la venta.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });

        function mostrarToast(mensaje, tipo = "exito") {
            const container = document.getElementById("toastContainer");
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