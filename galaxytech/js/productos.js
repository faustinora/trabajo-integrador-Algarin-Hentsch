document.addEventListener("DOMContentLoaded", () => {
    const inputBuscador = document.getElementById("inputBuscador");
    const selectEstado = document.getElementById("selectFiltroEstado");
    const btnBuscar = document.getElementById("btnBuscar");
    const tbody = document.getElementById("tabla-productos-body");

    if (tbody) {
        cargarProductos(false);

        if (inputBuscador) {
            inputBuscador.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    cargarProductos(true);
                }
            });
        }

        if (btnBuscar) {
            btnBuscar.addEventListener("click", (e) => {
                e.preventDefault();
                cargarProductos(true);
            });
        }

        if (selectEstado) {
            selectEstado.addEventListener("change", () => cargarProductos(false));
        }

        tbody.addEventListener("click", (e) => {
            const btnLimpiar = e.target.closest("[data-accion='limpiar-productos']");
            if (btnLimpiar) {
                limpiarYRecargar();
                return;
            }

            const btnBaja = e.target.closest("[data-accion='baja-producto']");
            if (btnBaja) {
                eliminarProducto(Number(btnBaja.dataset.id));
            }
        });
    }

    const formNuevoProducto = document.getElementById("formNuevoProducto");
    if (formNuevoProducto) {
        formNuevoProducto.addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                codigo: document.getElementById("codigo").value.trim(),
                nombre: document.getElementById("nombre").value.trim(),
                descripcion: document.getElementById("descripcion").value.trim(),
                precio: Number.parseFloat(document.getElementById("precio").value),
                stock: Number.parseInt(document.getElementById("stock").value, 10)
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
                    setTimeout(() => {
                        window.location.href = "indice.php";
                    }, 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }

    const formEditarProducto = document.getElementById("formEditarProducto");
    if (formEditarProducto) {
        const idInput = document.getElementById("id_producto");
        if (idInput) {
            cargarDatosProductoEditar(idInput.value);
        }

        formEditarProducto.addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_producto: document.getElementById("id_producto").value,
                codigo: document.getElementById("codigo").value.trim(),
                nombre: document.getElementById("nombre").value.trim(),
                descripcion: document.getElementById("descripcion").value.trim(),
                precio: Number.parseFloat(document.getElementById("precio").value),
                stock: Number.parseInt(document.getElementById("stock").value, 10)
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
                    setTimeout(() => {
                        window.location.href = "indice.php";
                    }, 1500);
                } else {
                    mostrarToast(resultado.message || "Error al actualizar.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }
});

async function cargarProductos(avisar = false) {
    try {
        const termino = document.getElementById("inputBuscador").value.trim();
        const estado = document.getElementById("selectFiltroEstado").value;

        const params = new URLSearchParams({ status: estado });
        if (termino) {
            params.set("q", termino);
        }

        const res = await fetch(`../api/productos/listar.php?${params.toString()}`);
        const data = await res.json();
        const productos = Array.isArray(data) ? data : (data.productos || data.data || []);

        const tbody = document.getElementById("tabla-productos-body");
        const totalElem = document.getElementById("totalRegistros");
        const spanValorInv = document.getElementById("spanValorInventario");

        if (avisar) {
            if (productos.length > 0) {
                mostrarToast(`Se encontraron ${productos.length} resultado(s).`, "exito");
            } else {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
        }

        if (!tbody) return;
        tbody.innerHTML = "";

        if (productos.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="celda-vacia">
                        <p class="texto-vacio">No se encontraron productos.</p>
                        <button type="button" class="secondary btn-inline-auto" data-accion="limpiar-productos">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 productos";
            if (spanValorInv) spanValorInv.textContent = formatoPesos.format(0);
            return;
        }

        let valorInventarioTotal = 0;

        productos.forEach((p) => {
            const idProd = Number(p.id_producto);
            const precio = Number.parseFloat(p.precio) || 0;
            const stock = Number.parseInt(p.stock, 10) || 0;
            const esActivo = Number.parseInt(p.activo, 10) === 1;

            if (esActivo) {
                valorInventarioTotal += precio * stock;
            }

            const badgeEstado = esActivo
                ? '<span class="badge-activo">Activo</span>'
                : '<span class="badge-inactivo">Inactivo</span>';

            const badgeStock = stock <= 5 && esActivo
                ? `<span class="badge-stock-bajo">${stock} (Bajo)</span>`
                : String(stock);

            const botonAccion = esActivo
                ? `<button type="button" data-accion="baja-producto" data-id="${idProd}" class="btn-tabla-eliminar">Dar de baja</button>`
                : `<span class="texto-inactivo-accion">Inactivo</span>`;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><code>${escapeHtml(p.codigo)}</code></td>
                <td><strong>${escapeHtml(p.nombre)}</strong></td>
                <td>$ ${precio.toFixed(2)}</td>
                <td>${badgeStock}</td>
                <td>${badgeEstado}</td>
                <td class="col-acciones">
                    <a href="editar.php?id=${idProd}" class="secondary btn-tabla-editar">Editar</a>
                    ${botonAccion}
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${productos.length} producto(s)`;
        if (spanValorInv) spanValorInv.textContent = formatoPesos.format(valorInventarioTotal);
    } catch (err) {
        mostrarToast("Error al cargar el inventario.", "error");
    }
}

async function cargarDatosProductoEditar(id) {
    try {
        const res = await fetch("../api/productos/listar.php?status=todos");
        const productos = await res.json();
        const producto = Array.isArray(productos) ? productos.find((p) => p.id_producto == id) : null;

        if (producto) {
            document.getElementById("codigo").value = producto.codigo || "";
            document.getElementById("nombre").value = producto.nombre || "";
            document.getElementById("descripcion").value = producto.descripcion || "";
            document.getElementById("precio").value = producto.precio ?? 0;
            document.getElementById("stock").value = producto.stock ?? 0;
        } else {
            mostrarToast("No se encontró el producto.", "error");
            setTimeout(() => {
                window.location.href = "indice.php";
            }, 1500);
        }
    } catch (err) {
        mostrarToast("Error al cargar los datos del producto.", "error");
    }
}

async function eliminarProducto(id_producto) {
    if (!confirm("¿Estás seguro de dar de baja el producto?")) return;
    try {
        const res = await fetch("../api/productos/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_producto })
        });
        const data = await res.json();
        if (data.success) {
            mostrarToast("Producto dado de baja.", "exito");
            cargarProductos(false);
        } else {
            mostrarToast(data.message || "No se pudo dar de baja.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión.", "error");
    }
}

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

function limpiarYRecargar() {
    const inputBuscador = document.getElementById("inputBuscador");
    if (inputBuscador) inputBuscador.value = "";
    const selectEstado = document.getElementById("selectFiltroEstado");
    if (selectEstado) selectEstado.value = "1";
    cargarProductos(false);
}

const formatoPesos = new Intl.NumberFormat("es-AR", {
    style: "currency",
    currency: "ARS",
    maximumFractionDigits: 0
});

function escapeHtml(text) {
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
    return String(text || "").replace(/[&<>"']/g, (m) => map[m]);
}