let listaProductosGlobal = [];

/* ==========================================================================
   INICIALIZACIÓN VISTA ACTIVA
   ========================================================================== */
document.addEventListener("DOMContentLoaded", async () => {
    // 1. Lógica vista de Historial de Ventas
    const tablaVentasBody = document.getElementById("tabla-ventas-body");
    if (tablaVentasBody) {
        cargarVentas(false);

        const inputBuscador = document.getElementById("inputBuscador");
        const btnBuscar = document.getElementById("btnBuscar");

        if (inputBuscador) {
            inputBuscador.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    cargarVentas(true);
                }
            });
        }

        if (btnBuscar) {
            btnBuscar.addEventListener("click", (e) => {
                e.preventDefault();
                cargarVentas(true);
            });
        }
    }

    // 2. Lógica Registrar Nueva Venta / Cobro de Servicio
    const formNuevaVenta = document.getElementById("formNuevaVenta");
    if (formNuevaVenta) {
        await cargarSelectClientes();
        await cargarProductosGlobal();

        // Leer parámetros URL si viene desde servicios
        const params = new URLSearchParams(window.location.search);
        const idServicioParam = params.get("id_servicio");
        const idClienteParam = params.get("id_cliente");
        const costoServicio = parseFloat(params.get("costo")) || 0;
        const descEquipo = params.get("equipo") || "Servicio Técnico";

        if (idClienteParam) {
            const selectCliente = document.getElementById("selectCliente");
            if (selectCliente) selectCliente.value = idClienteParam;
        }

        formNuevaVenta.dataset.idServicio = idServicioParam || "";
        formNuevaVenta.dataset.costoServicio = costoServicio;

        // Si viene de un servicio, mostramos el panel informativo
        if (idServicioParam) {
            const infoServicio = document.getElementById("infoServicioCobro");
            const txtDetalle = document.getElementById("txtDetalleEquipo");
            const lblCosto = document.getElementById("lblCostoServicio");

            if (infoServicio) infoServicio.style.display = "block";
            if (txtDetalle) txtDetalle.textContent = `Equipo / Reparación: ${descEquipo}`;
            if (lblCosto) lblCosto.textContent = `$${costoServicio.toFixed(2)}`;
            
            calcularTotalGeneral();
        } else {
            agregarFilaCarrito(); // Fila vacía inicial solo si es venta normal de productos
        }

        const btnAddProducto = document.getElementById("btnAddProducto");
        if (btnAddProducto) {
            btnAddProducto.addEventListener("click", () => {
                agregarFilaCarrito();
                calcularTotalGeneral();
            });
        }

        formNuevaVenta.addEventListener("submit", guardarNuevaVenta);
    }

    // Lógica del modal de cliente rápido en nueva venta
    const modalCliente = document.getElementById("modalNuevoCliente");
    const btnAbrirModal = document.getElementById("btnAbrirModalCliente");
    const btnCerrarModal = document.getElementById("btnCerrarModalCliente");
    const formClienteModal = document.getElementById("formNuevoClienteModal");

    if (btnAbrirModal && modalCliente) {
        btnAbrirModal.addEventListener("click", () => {
            modalCliente.style.display = "flex";
        });
    }

    if (btnCerrarModal && modalCliente) {
        btnCerrarModal.addEventListener("click", () => {
            modalCliente.style.display = "none";
            if (formClienteModal) formClienteModal.reset();
        });
    }

    if (formClienteModal) {
        formClienteModal.addEventListener("submit", async (e) => {
            e.preventDefault();
            const telefonoInput = document.getElementById("cli_telefono").value.trim();

            if (!/^\d+$/.test(telefonoInput)) {
                mostrarToast("El teléfono debe contener únicamente números.", "error");
                return;
            }

            const datos = {
                nombre: document.getElementById("cli_nombre").value.trim(),
                apellido: document.getElementById("cli_apellido").value.trim(),
                telefono: telefonoInput,
                email: document.getElementById("cli_email").value.trim()
            };

            try {
                const res = await fetch("../api/clientes/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const data = await res.json();

                if (res.ok && data.success) {
                    mostrarToast("Cliente registrado con éxito.", "exito");
                    formClienteModal.reset();
                    modalCliente.style.display = "none";
                    await cargarSelectClientes();
                    
                    if (data.id_cliente) {
                        const selectCliente = document.getElementById("selectCliente");
                        if (selectCliente) selectCliente.value = data.id_cliente;
                    }
                } else {
                    mostrarToast(data.message || "No se pudo registrar el cliente.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión al registrar cliente.", "error");
            }
        });
    }
});

/* ==========================================================================
   MÓDULO 1: HISTORIAL Y ANULACIÓN DE VENTAS
   ========================================================================== */
async function cargarVentas(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const termino = inputBuscador ? inputBuscador.value.trim() : "";

        const url = termino 
            ? `../api/ventas/listar.php?q=${encodeURIComponent(termino)}` 
            : "../api/ventas/listar.php";
            
        const res = await fetch(url);
        const data = await res.json();
        const ventas = Array.isArray(data) ? data : (data.ventas || data.data || []);

        const tbody = document.getElementById("tabla-ventas-body");
        const totalElem = document.getElementById("totalRegistros");
        const spanTotalVentas = document.getElementById("spanTotalVentas");

        if (avisar) {
            if (ventas.length > 0) {
                mostrarToast(`Se encontraron ${ventas.length} resultado(s).`, "exito");
            } else {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
        }

        if (!tbody) return;
        tbody.innerHTML = "";

        if (ventas.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" style="text-align: center; padding: 2rem;">
                        <p style="margin-bottom: 1rem; color: #94a3b8;">No hay ventas registradas.</p>
                        <button type="button" class="secondary" onclick="limpiarYRecargar()" style="display: inline-block; width: auto; padding: 0.5rem 1.2rem;">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 ventas";
            if (spanTotalVentas) spanTotalVentas.textContent = "$0.00";
            return;
        }

        let recaudacionTotal = 0;

        ventas.forEach(v => {
            const idVenta = v.id_venta;
            const totalNum = parseFloat(v.total || 0);
            recaudacionTotal += totalNum;

            const clienteNombre = v.apellido 
                ? `${escapeHtml(v.apellido)}, ${escapeHtml(v.nombre)}` 
                : 'Consumidor Final';
            const vendedorNombre = escapeHtml(v.vendedor || 'Sistema');

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${escapeHtml(v.fecha_venta)}</td>
                <td><strong>${clienteNombre}</strong></td>
                <td><span style="color: #64748b; font-size: 0.9rem;">${vendedorNombre}</span></td>
                <td>$ ${totalNum.toFixed(2)}</td>
                <td class="col-acciones">
                    <button type="button" onclick="anularVenta(${idVenta})" class="btn-tabla-eliminar">Anular</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${ventas.length} venta(s)`;
        if (spanTotalVentas) spanTotalVentas.textContent = `$${recaudacionTotal.toFixed(2)}`;
    } catch (err) {
        mostrarToast("Error al cargar el historial de ventas.", "error");
    }
}

async function anularVenta(id_venta) {
    if (!confirm("¿Estás seguro de que querés anular esta venta? Se restaurará el stock de los productos.")) return;
    
    try {
        const res = await fetch("../api/ventas/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_venta })
        });
        const data = await res.json();
        
        if (data.success) {
            mostrarToast("Venta anulada y stock restaurado.", "exito");
            cargarVentas(false);
        } else {
            mostrarToast(data.message || "No se pudo anular la venta.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión con el servidor.", "error");
    }
}

function limpiarYRecargar() {
    const inputBuscador = document.getElementById("inputBuscador");
    if (inputBuscador) inputBuscador.value = "";
    cargarVentas(false);
}

/* ==========================================================================
   MÓDULO 2: REGISTRO DE NUEVA VENTA Y CARRITO
   ========================================================================== */
async function cargarSelectClientes() {
    try {
        const res = await fetch("../api/clientes/listar.php");
        const clientes = await res.json();
        const select = document.getElementById("selectCliente");
        if (!select) return;

        select.innerHTML = '<option value="" disabled selected>-- Seleccione un cliente --</option>';
        clientes.forEach(c => {
            select.innerHTML += `<option value="${c.id_cliente}">${escapeHtml(c.nombre)} ${escapeHtml(c.apellido)} (Tel: ${escapeHtml(c.telefono)})</option>`;
        });
    } catch (err) {
        mostrarToast("Error al cargar la lista de clientes.", "error");
    }
}

async function cargarProductosGlobal() {
    try {
        const res = await fetch("../api/productos/listar.php");
        const data = await res.json();
        listaProductosGlobal = Array.isArray(data) ? data : (data.productos || data.data || []);
    } catch (err) {
        mostrarToast("Error al cargar el catálogo de productos.", "error");
    }
}

function agregarFilaCarrito() {
    const tbody = document.getElementById("tablaCarritoBody");
    if (!tbody) return;

    const tr = document.createElement("tr");
    tr.className = "fila-carrito";

    let optionsHtml = '<option value="" disabled selected>-- Seleccione producto --</option>';
    listaProductosGlobal.forEach(p => {
        if (parseInt(p.stock) > 0) {
            optionsHtml += `<option value="${p.id_producto}" data-precio="${p.precio}" data-stock="${p.stock}">${escapeHtml(p.nombre)} (Stock: ${p.stock})</option>`;
        }
    });

    tr.innerHTML = `
        <td><select class="prod-select" required style="margin-bottom:0;">${optionsHtml}</select></td>
        <td class="lbl-precio">$0.00</td>
        <td><input type="number" class="prod-cant" min="1" value="1" required style="margin-bottom:0;"></td>
        <td class="lbl-subtotal">$0.00</td>
        <td style="text-align: center;">
            <button type="button" class="btn-accion-tabla" onclick="eliminarFilaCarrito(this)">Eliminar</button>
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

    inputCant.addEventListener("input", () => actualizarFilaSubtotal(tr));

    tbody.appendChild(tr);
}

function eliminarFilaCarrito(boton) {
    boton.closest("tr").remove();
    calcularTotalGeneral();
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
        mostrarToast(`Stock máximo disponible: ${stockMax} unidades.`, "error");
        cantidad = stockMax;
        cantInput.value = stockMax;
    }

    const subtotal = precio * cantidad;
    lblSubtotal.textContent = `$${subtotal.toFixed(2)}`;
    calcularTotalGeneral();
}

function calcularTotalGeneral() {
    const form = document.getElementById("formNuevaVenta");
    const costoServicio = form && form.dataset.costoServicio ? parseFloat(form.dataset.costoServicio) || 0 : 0;

    let totalProductos = 0;
    document.querySelectorAll(".fila-carrito").forEach(tr => {
        const select = tr.querySelector(".prod-select");
        const cantInput = tr.querySelector(".prod-cant");
        if (select && select.selectedIndex > 0) {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.dataset.precio) {
                totalProductos += (parseFloat(opt.dataset.precio) || 0) * (parseInt(cantInput.value) || 0);
            }
        }
    });

    const totalGeneral = costoServicio + totalProductos;
    const lblTotal = document.getElementById("lblTotalVenta");
    if (lblTotal) lblTotal.textContent = `$${totalGeneral.toFixed(2)}`;
}

async function guardarNuevaVenta(e) {
    e.preventDefault();

    const form = document.getElementById("formNuevaVenta");
    const id_cliente = document.getElementById("selectCliente").value;
    const id_servicio = form.dataset.idServicio ? parseInt(form.dataset.idServicio) : null;
    const filas = document.querySelectorAll(".fila-carrito");
    const productos = [];

    filas.forEach(tr => {
        const selectProd = tr.querySelector(".prod-select");
        const cantInput = tr.querySelector(".prod-cant");
        if (selectProd && selectProd.value && cantInput) {
            const id_producto = selectProd.value;
            const cantidad = parseInt(cantInput.value);
            if (id_producto && cantidad > 0) {
                productos.push({ id_producto, cantidad });
            }
        }
    });

    if (id_cliente <= 0) {
        mostrarToast("Debe seleccionar un cliente.", "error");
        return;
    }

    if (!id_servicio && productos.length === 0) {
        mostrarToast("Debe agregar al menos un producto a la venta.", "error");
        return;
    }

    try {
        const res = await fetch("../api/ventas/guardar.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_cliente, id_servicio, productos })
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
}

/* ==========================================================================
   UTILIDADES COMPARTIDAS
   ========================================================================== */
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

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text || '').replace(/[&<>"']/g, m => map[m]);
}