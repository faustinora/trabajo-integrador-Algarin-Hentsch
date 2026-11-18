document.addEventListener("DOMContentLoaded", () => {

    // 1. Lógica para listar (indice)
    const inputBuscador = document.getElementById("inputBuscador");
    const selectEstado = document.getElementById("selectFiltroEstado");
    const btnBuscar = document.getElementById("btnBuscar");

    if (document.getElementById("tabla-productos-body")) {
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
    }

    // 2. Lógica para form NUEVO
    const formNuevoProducto = document.getElementById("formNuevoProducto");
    if (formNuevoProducto) {
        formNuevoProducto.addEventListener("submit", async (e) => {
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
    }

    // 3. Lógica para form EDITAR
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
    }
});


// --- FUNCIONES AUXILIARES ---

async function cargarProductos(avisar = false) {
    try {
        const termino = document.getElementById("inputBuscador").value.trim();
        const estado = document.getElementById("selectFiltroEstado").value;

        let url = `../api/productos/listar.php?status=${estado}`;
        if (termino) url += `&q=${encodeURIComponent(termino)}`;

        const res = await fetch(url);
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

        tbody.innerHTML = "";

        if (productos.length === 0) {
            if (avisar) {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
            tbody.innerHTML = `
                <tr>
                    <td colspan='6' style='text-align: center; padding: 2rem;'>
                        <p style="margin-bottom: 1rem; color: #94a3b8;">No se encontraron productos.</p>
                        <button type="button" class="secondary" onclick="limpiarYRecargar()" style="display: inline-block; width: auto; padding: 0.5rem 1.2rem;">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 productos";
            if (spanValorInv) spanValorInv.textContent = "$0.00";
            return;
        }

        let valorInventarioTotal = 0;

        productos.forEach(p => {
            const idProd = p.id_producto;
            const precio = parseFloat(p.precio) || 0;
            const stock = parseInt(p.stock) || 0;
            const esActivo = parseInt(p.activo) === 1;

            if (esActivo) {
                valorInventarioTotal += precio * stock;
            }

            const badgeEstado = esActivo 
                ? '<span style="background-color: #bbf7d0; color: #166534; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Activo</span>'
                : '<span style="background-color: #fecaca; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Inactivo</span>';

            const badgeStock = stock <= 5 && esActivo
                ? `<span style="background-color: #fef08a; color: #854d0e; padding: 2px 6px; border-radius: 4px; font-weight: bold;">${stock} (Bajo)</span>`
                : stock;

            const botonAccion = esActivo
                ? `<button onclick="eliminarProducto(${idProd})" class="btn-tabla-eliminar">Dar de baja</button>`
                : `<span style="color: #64748b; font-size: 0.85rem;">Inactivo</span>`;

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
        if (spanValorInv) spanValorInv.textContent = `$${valorInventarioTotal.toFixed(2)}`;
    } catch (err) {
        mostrarToast("Error al cargar el inventario.", "error");
    }
}

async function cargarDatosProductoEditar(id) {
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
}

async function eliminarProducto(id_producto) {
    if (!confirm(`¿Estás seguro de dar de baja el producto?`)) return;
    try {
        const res = await fetch("../api/productos/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_producto })
        });
        const data = await res.json();
        if (data.success) {
            mostrarToast("Producto dado de baja.", "exito");
            cargarProductos();
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

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text || '').replace(/[&<>"']/g, m => map[m]);
}