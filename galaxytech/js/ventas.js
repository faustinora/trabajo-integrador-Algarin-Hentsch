let listaProductosGlobal = [];
//inicio
document.addEventListener("DOMContentLoaded", async () => {
    //Historial de Ventas
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

        tablaVentasBody.addEventListener("click", (e) => {
            const btnLimpiar = e.target.closest("[data-accion='limpiar-ventas']");
            if (btnLimpiar) {
                limpiarYRecargar();
                return;
            }

            const btnAnular = e.target.closest("[data-accion='anular-venta']");
            if (btnAnular) {
                const idVenta = Number(btnAnular.dataset.id);
                if (idVenta > 0) {
                    anularVenta(idVenta);
                }
            }
        });
    }

    //Nueva Venta / Cobro de Servicio
    const formNuevaVenta = document.getElementById("formNuevaVenta");
    if (formNuevaVenta) {
        await cargarSelectClientes();
        await cargarProductosGlobal();

        const params = new URLSearchParams(window.location.search);
        const idServicioParam = params.get("id_servicio");
        const idClienteParam = params.get("id_cliente");
        const costoServicio = Number.parseFloat(params.get("costo")) || 0;
        const descEquipo = params.get("equipo") || "Servicio Técnico";

        if (idClienteParam) {
            const selectCliente = document.getElementById("selectCliente");
            if (selectCliente) selectCliente.value = idClienteParam;
        }

        formNuevaVenta.dataset.idServicio = idServicioParam || "";
        formNuevaVenta.dataset.costoServicio = String(costoServicio);

        if (idServicioParam) {
            const infoServicio = document.getElementById("infoServicioCobro");
            const txtDetalle = document.getElementById("txtDetalleEquipo");
            const lblCosto = document.getElementById("lblCostoServicio");

            if (infoServicio) infoServicio.classList.add("activo");
            if (txtDetalle) txtDetalle.textContent = `Equipo / Reparación: ${descEquipo}`;
            if (lblCosto) lblCosto.textContent = `$${costoServicio.toFixed(2)}`;

            calcularTotalGeneral();
        } else {
            agregarFilaCarrito();
        }

        const btnAddProducto = document.getElementById("btnAddProducto");
        if (btnAddProducto) {
            btnAddProducto.addEventListener("click", () => {
                agregarFilaCarrito();
                calcularTotalGeneral();
            });
        }

        const tbodyCarrito = document.getElementById("tablaCarritoBody");
        if (tbodyCarrito) {
            tbodyCarrito.addEventListener("click", (e) => {
                const btnEliminar = e.target.closest("[data-accion='eliminar-fila']");
                if (btnEliminar) {
                    const fila = btnEliminar.closest("tr");
                    if (fila) {
                        fila.remove();
                        calcularTotalGeneral();
                    }
                }
            });
        }

        formNuevaVenta.addEventListener("submit", guardarNuevaVenta);
    }

    // cliente rápido
    const modalCliente = document.getElementById("modalNuevoCliente");
    const btnAbrirModal = document.getElementById("btnAbrirModalCliente");
    const btnCerrarModal = document.getElementById("btnCerrarModalCliente");
    const formClienteModal = document.getElementById("formNuevoClienteModal");

    if (btnAbrirModal && modalCliente) {
        btnAbrirModal.addEventListener("click", () => {
            modalCliente.classList.add("activo");
        });
    }

    if (btnCerrarModal && modalCliente) {
        btnCerrarModal.addEventListener("click", () => {
            modalCliente.classList.remove("activo");
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
                    if (modalCliente) modalCliente.classList.remove("activo");
                    await cargarSelectClientes();

                    if (data.id_cliente) {
                        const selectCliente = document.getElementById("selectCliente");
                        if (selectCliente) selectCliente.value = String(data.id_cliente);
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
                    <td colspan="5" class="celda-vacia">
                        <p class="texto-vacio">No hay ventas registradas.</p>
                        <button type="button" class="secondary btn-inline-auto" data-accion="limpiar-ventas">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 ventas";
            if (spanTotalVentas) spanTotalVentas.textContent = formatoPesos.format(0);
            return;
        }

        let recaudacionTotal = 0;

        ventas.forEach((v) => {
            const idVenta = Number(v.id_venta);
            const totalNum = Number.parseFloat(v.total || 0);
            recaudacionTotal += totalNum;

            const clienteNombre = v.apellido
                ? `${escapeHtml(v.apellido)}, ${escapeHtml(v.nombre)}`
                : "Consumidor Final";
            const vendedorNombre = escapeHtml(v.vendedor || "Sistema");

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${escapeHtml(v.fecha_venta)}</td>
                <td><strong>${clienteNombre}</strong></td>
                <td><span class="texto-vendedor">${vendedorNombre}</span></td>
                <td>$ ${totalNum.toFixed(2)}</td>
                <td class="col-acciones">
                    <button type="button" data-accion="anular-venta" data-id="${idVenta}" class="btn-tabla-eliminar">Anular</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${ventas.length} venta(s)`;
        if (spanTotalVentas) spanTotalVentas.textContent = formatoPesos.format(recaudacionTotal);
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

        if (res.ok && data.success) {
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

async function cargarSelectClientes() {
    try {
        const res = await fetch("../api/clientes/listar.php");
        const clientes = await res.json();
        const select = document.getElementById("selectCliente");
        if (!select) return;

        select.innerHTML = '<option value="" disabled selected>-- Seleccione un cliente --</option>';
        clientes.forEach((c) => {
            const id = Number(c.id_cliente);
            const nombre = escapeHtml(c.nombre);
            const apellido = escapeHtml(c.apellido);
            const telefono = escapeHtml(c.telefono || "Sin teléfono");
            select.innerHTML += `<option value="${id}">${nombre} ${apellido} (Tel: ${telefono})</option>`;
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
    listaProductosGlobal.forEach((p) => {
        const stock = Number.parseInt(p.stock, 10) || 0;
        if (stock > 0) {
            const idProd = Number(p.id_producto);
            const precio = Number.parseFloat(p.precio) || 0;
            const nombreProd = escapeHtml(p.nombre);
            optionsHtml += `<option value="${idProd}" data-precio="${precio}" data-stock="${stock}">${nombreProd} (Stock: ${stock})</option>`;
        }
    });

    tr.innerHTML = `
        <td><select class="prod-select input-tabla-compacto" required>${optionsHtml}</select></td>
        <td class="lbl-precio">$0.00</td>
        <td><input type="number" class="prod-cant input-tabla-compacto" min="1" value="1" required></td>
        <td class="lbl-subtotal">$0.00</td>
        <td class="col-acciones">
            <button type="button" class="btn-accion-tabla" data-accion="eliminar-fila">Eliminar</button>
        </td>
    `;

    const selectProd = tr.querySelector(".prod-select");
    const inputCant = tr.querySelector(".prod-cant");
    const lblPrecio = tr.querySelector(".lbl-precio");

    selectProd.addEventListener("change", (e) => {
        const opt = e.target.options[e.target.selectedIndex];
        const precio = Number.parseFloat(opt.dataset.precio) || 0;
        lblPrecio.textContent = `$${precio.toFixed(2)}`;
        actualizarFilaSubtotal(tr);
    });

    inputCant.addEventListener("input", () => actualizarFilaSubtotal(tr));

    tbody.appendChild(tr);
}

function actualizarFilaSubtotal(tr) {
    const select = tr.querySelector(".prod-select");
    const cantInput = tr.querySelector(".prod-cant");
    const lblSubtotal = tr.querySelector(".lbl-subtotal");

    const opt = select.options[select.selectedIndex];
    const precio = opt && opt.dataset.precio ? Number.parseFloat(opt.dataset.precio) : 0;
    const stockMax = opt && opt.dataset.stock ? Number.parseInt(opt.dataset.stock, 10) : 9999;
    let cantidad = Number.parseInt(cantInput.value, 10) || 0;

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
    const costoServicio = form && form.dataset.costoServicio ? Number.parseFloat(form.dataset.costoServicio) || 0 : 0;

    let totalProductos = 0;
    document.querySelectorAll(".fila-carrito").forEach((tr) => {
        const select = tr.querySelector(".prod-select");
        const cantInput = tr.querySelector(".prod-cant");
        if (select && select.selectedIndex > 0) {
            const opt = select.options[select.selectedIndex];
            if (opt && opt.dataset.precio) {
                totalProductos += (Number.parseFloat(opt.dataset.precio) || 0) * (Number.parseInt(cantInput.value, 10) || 0);
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
    const id_cliente = Number(document.getElementById("selectCliente").value || 0);
    const id_servicio = form.dataset.idServicio ? Number.parseInt(form.dataset.idServicio, 10) : null;
    const filas = document.querySelectorAll(".fila-carrito");
    const productos = [];

    filas.forEach((tr) => {
        const selectProd = tr.querySelector(".prod-select");
        const cantInput = tr.querySelector(".prod-cant");
        if (selectProd && selectProd.value && cantInput) {
            const id_producto = Number(selectProd.value);
            const cantidad = Number.parseInt(cantInput.value, 10);
            if (id_producto > 0 && cantidad > 0) {
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
            setTimeout(() => {
                window.location.href = "indice.php";
            }, 1500);
        } else {
            mostrarToast(resultado.message || "Error al registrar la venta.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión con el servidor.", "error");
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

const formatoPesos = new Intl.NumberFormat("es-AR", {
    style: "currency",
    currency: "ARS",
    maximumFractionDigits: 0
});

function escapeHtml(text) {
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
    return String(text || "").replace(/[&<>"']/g, (m) => map[m]);
}