const ESTADOS_SERVICIO = ["pendiente", "en proceso", "completado", "entregado"];

document.addEventListener("DOMContentLoaded", () => {
    if (document.getElementById("tabla-servicios-body")) {
        inicializarIndiceServicios();
    }
    if (document.getElementById("formNuevoServicio")) {
        inicializarNuevoServicio();
    }
    if (document.getElementById("formEditarServicio")) {
        inicializarEditarServicio();
    }
});

// --- LISTADO / ÍNDICE ---
function inicializarIndiceServicios() {
    cargarServicios(false);

    const inputBuscador = document.getElementById("inputBuscador");
    const selectEstado = document.getElementById("selectFiltroEstado");
    const btnBuscar = document.getElementById("btnBuscar");
    const tbody = document.getElementById("tabla-servicios-body");

    if (inputBuscador) {
        inputBuscador.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                cargarServicios(true);
            }
        });
    }

    if (btnBuscar) {
        btnBuscar.addEventListener("click", (e) => {
            e.preventDefault();
            cargarServicios(true);
        });
    }

    if (selectEstado) {
        selectEstado.addEventListener("change", () => cargarServicios(false));
    }

    if (tbody) {
        tbody.addEventListener("click", (e) => {
            const btnLimpiar = e.target.closest("[data-accion='limpiar']");
            if (btnLimpiar) {
                limpiarYRecargar();
                return;
            }

            const btnBaja = e.target.closest("[data-accion='baja']");
            if (btnBaja) {
                const idServicio = Number(btnBaja.dataset.id);
                if (idServicio > 0) {
                    darDeBajaServicio(idServicio);
                }
            }
        });
    }
}

function renderBadgeEstado(estado) {
    const est = ESTADOS_SERVICIO.includes(String(estado || "").toLowerCase().trim())
        ? String(estado).toLowerCase().trim()
        : "pendiente";

    const clases = {
        "pendiente": "badge-estado badge-pendiente",
        "en proceso": "badge-estado badge-proceso",
        "completado": "badge-estado badge-completado",
        "entregado": "badge-estado badge-entregado"
    };

    const etiquetas = {
        "pendiente": "Pendiente",
        "en proceso": "En Proceso",
        "completado": "Completado",
        "entregado": "Entregado"
    };

    return `<span class="${clases[est]}">${escapeHtml(etiquetas[est])}</span>`;
}

async function cargarServicios(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const selectEstado = document.getElementById("selectFiltroEstado");

        const termino = inputBuscador ? inputBuscador.value.trim() : "";
        const estado = selectEstado ? selectEstado.value : "todos";

        const params = new URLSearchParams({ status: estado });
        if (termino) {
            params.set("q", termino);
        }

        const res = await fetch(`../api/servicios/listar.php?${params.toString()}`);
        const data = await res.json();
        const servicios = Array.isArray(data) ? data : (data.servicios || data.data || []);

        const tbody = document.getElementById("tabla-servicios-body");
        const totalElem = document.getElementById("totalRegistros");

        if (avisar) {
            if (servicios.length > 0) {
                mostrarToast(`Se encontraron ${servicios.length} resultado(s).`, "exito");
            } else {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
        }

        if (!tbody) return;
        tbody.innerHTML = "";

        if (servicios.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="6" class="celda-vacia">
                        <p class="texto-vacio">No se encontraron servicios técnicos.</p>
                        <button type="button" class="secondary btn-inline-auto" data-accion="limpiar">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 servicios";
            return;
        }

        servicios.forEach((s) => {
            const idServ = Number(s.id_servicio);
            const idCliente = Number(s.id_cliente);
            const costo = Number.parseFloat(s.costo) || 0;
            const cliente = `${escapeHtml(s.cliente_nombre)} ${escapeHtml(s.cliente_apellido)}`;
            const est = String(s.estado || "").toLowerCase().trim();
            const badgeEstado = renderBadgeEstado(est);

            let accionesHtml = "";

            if (est === "completado") {
                const queryCobro = new URLSearchParams({
                    id_servicio: String(idServ),
                    id_cliente: String(idCliente),
                    costo: String(costo),
                    equipo: String(s.equipo_descripcion || "")
                });
                accionesHtml = `
                    <a href="../ventas/nueva.php?${queryCobro.toString()}" class="btn-principal btn-accion-sm btn-cobrar">Cobrar</a>
                `;
            } else if (est === "entregado") {
                accionesHtml = `<span class="texto-entregado">Entregado</span>`;
            } else {
                accionesHtml = `
                    <a href="editar.php?id=${idServ}" class="btn-secundario btn-accion-sm">Editar</a>
                    <button type="button" data-accion="baja" data-id="${idServ}" class="btn-tabla-eliminar btn-accion-sm">Baja</button>
                `;
            }

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><small>${escapeHtml(s.fecha_ingreso)}</small></td>
                <td><strong>${cliente}</strong></td>
                <td>
                    <div>${escapeHtml(s.equipo_descripcion)}</div>
                    <small class="texto-secundario">${escapeHtml(s.diagnostico || "Sin diagnóstico previo")}</small>
                </td>
                <td>$ ${costo.toFixed(2)}</td>
                <td>${badgeEstado}</td>
                <td class="col-acciones">${accionesHtml}</td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${servicios.length} servicio(s)`;
    } catch (err) {
        mostrarToast("Error al cargar los servicios técnicos.", "error");
    }
}

async function darDeBajaServicio(id_servicio) {
    if (!confirm("¿Estás seguro de dar de baja este servicio técnico?")) return;

    try {
        const res = await fetch("../api/servicios/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_servicio })
        });
        const data = await res.json();

        if (res.ok && data.success) {
            mostrarToast("Servicio dado de baja correctamente.", "exito");
            cargarServicios(false);
        } else {
            mostrarToast(data.message || "No se pudo dar de baja.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión.", "error");
    }
}

function limpiarYRecargar() {
    const inputBuscador = document.getElementById("inputBuscador");
    if (inputBuscador) inputBuscador.value = "";
    const selectEstado = document.getElementById("selectFiltroEstado");
    if (selectEstado) selectEstado.value = "todos";
    cargarServicios(false);
}

// --- MODAL COMPARTIDO DE CLIENTE ---
function configurarModalCliente(onClienteCreado = null) {
    const btnNuevoClienteModal = document.getElementById("btnNuevoClienteModal");
    const modalCliente = document.getElementById("modalCliente");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const formModalCliente = document.getElementById("formModalCliente");

    if (!btnNuevoClienteModal || !modalCliente || !formModalCliente) return;

    btnNuevoClienteModal.addEventListener("click", () => {
        modalCliente.classList.add("activo");
    });

    if (btnCerrarModal) {
        btnCerrarModal.addEventListener("click", () => {
            modalCliente.classList.remove("activo");
            formModalCliente.reset();
        });
    }

    formModalCliente.addEventListener("submit", async (e) => {
        e.preventDefault();
        const apellido = document.getElementById("modal_apellido").value.trim();
        const nombre = document.getElementById("modal_nombre").value.trim();
        const telefono = document.getElementById("modal_telefono").value.trim();
        const email = document.getElementById("modal_email").value.trim();

        if (!apellido || !nombre) {
            mostrarToast("Nombre y apellido son obligatorios.", "error");
            return;
        }

        try {
            const res = await fetch("../api/clientes/guardar.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nombre, apellido, telefono, email })
            });
            const data = await res.json();

            if (res.ok && data.success) {
                mostrarToast("Cliente registrado.", "exito");
                modalCliente.classList.remove("activo");
                formModalCliente.reset();
                if (typeof onClienteCreado === "function") {
                    await onClienteCreado(data);
                }
            } else {
                mostrarToast(data.message || "No se pudo registrar.", "error");
            }
        } catch (err) {
            mostrarToast("Error de conexión al registrar cliente.", "error");
        }
    });
}

// --- NUEVO SERVICIO ---
function inicializarNuevoServicio() {
    cargarSelectClientes();

    configurarModalCliente(async (data) => {
        await cargarSelectClientes();
        if (data && data.id_cliente) {
            const selectCliente = document.getElementById("selectCliente");
            if (selectCliente) selectCliente.value = data.id_cliente;
        }
    });

    const formNuevo = document.getElementById("formNuevoServicio");
    if (!formNuevo) return;

    formNuevo.addEventListener("submit", async (e) => {
        e.preventDefault();

        const costoVal = Number.parseFloat(document.getElementById("costo").value);
        const datos = {
            id_cliente: Number(document.getElementById("selectCliente").value),
            equipo_descripcion: document.getElementById("equipo_descripcion").value.trim(),
            diagnostico: document.getElementById("diagnostico").value.trim(),
            costo: Number.isNaN(costoVal) ? 0 : costoVal,
            estado: document.getElementById("estado").value
        };

        if (!datos.id_cliente || !datos.equipo_descripcion) {
            mostrarToast("Seleccione un cliente y describa el equipo.", "error");
            return;
        }

        if (datos.costo < 0) {
            mostrarToast("El costo no puede ser negativo.", "error");
            return;
        }

        try {
            const res = await fetch("../api/servicios/guardar.php", {
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
                mostrarToast(resultado.message || "Error al registrar el servicio.", "error");
            }
        } catch (err) {
            mostrarToast("Error de conexión con el servidor.", "error");
        }
    });
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
            select.innerHTML += `<option value="${id}">${nombre}, ${apellido} (${telefono})</option>`;
        });
    } catch (err) {
        mostrarToast("Error al cargar clientes", "error");
    }
}

// --- EDITAR SERVICIO ---
function inicializarEditarServicio() {
    const id = Number(document.getElementById("id_servicio")?.value || 0);
    if (id <= 0) return;

    (async () => {
        try {
            const res = await fetch("../api/servicios/listar.php?status=todos");
            const servicios = await res.json();
            const servicio = Array.isArray(servicios)
                ? servicios.find((s) => Number(s.id_servicio) === id)
                : null;

            if (servicio) {
                document.getElementById("equipo_descripcion").value = servicio.equipo_descripcion || "";
                document.getElementById("diagnostico").value = servicio.diagnostico || "";
                document.getElementById("costo").value = servicio.costo ?? 0;
                document.getElementById("estado").value = ESTADOS_SERVICIO.includes(servicio.estado)
                    ? servicio.estado
                    : "pendiente";
            } else {
                mostrarToast("No se encontró el servicio.", "error");
                setTimeout(() => {
                    window.location.href = "indice.php";
                }, 1500);
            }
        } catch (err) {
            mostrarToast("Error al cargar los datos del servicio.", "error");
        }
    })();

    const formEditar = document.getElementById("formEditarServicio");
    if (!formEditar) return;

    formEditar.addEventListener("submit", async (e) => {
        e.preventDefault();

        const costoVal = Number.parseFloat(document.getElementById("costo").value);
        const datos = {
            id_servicio: id,
            equipo_descripcion: document.getElementById("equipo_descripcion").value.trim(),
            diagnostico: document.getElementById("diagnostico").value.trim(),
            costo: Number.isNaN(costoVal) ? 0 : costoVal,
            estado: document.getElementById("estado").value
        };

        if (!datos.equipo_descripcion) {
            mostrarToast("La descripción del equipo es obligatoria.", "error");
            return;
        }

        if (datos.costo < 0) {
            mostrarToast("El costo no puede ser negativo.", "error");
            return;
        }

        try {
            const res = await fetch("../api/servicios/actualizar.php", {
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

// --- UTILIDADES GLOBALES ---
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
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
    return String(text || "").replace(/[&<>"']/g, (m) => map[m]);
}