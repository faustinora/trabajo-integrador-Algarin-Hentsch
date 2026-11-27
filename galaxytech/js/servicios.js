document.addEventListener("DOMContentLoaded", () => {
    // 1. Detección automática de la vista activa según los elementos del DOM
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


//indice lista
function inicializarIndiceServicios() {
    cargarServicios(false);

    const inputBuscador = document.getElementById("inputBuscador");
    const selectEstado = document.getElementById("selectFiltroEstado");
    const btnBuscar = document.getElementById("btnBuscar");

    const btnNuevoClienteModal = document.getElementById("btnNuevoClienteModal");
    const modalCliente = document.getElementById("modalCliente");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const formModalCliente = document.getElementById("formModalCliente");

    if (btnNuevoClienteModal && modalCliente) {
        btnNuevoClienteModal.addEventListener("click", () => {
        modalCliente.style.display = "flex";
        }); 
    }

    if (btnCerrarModal && modalCliente) {
        btnCerrarModal.addEventListener("click", () => {
        modalCliente.style.display = "none";
        formModalCliente.reset();
        });
    }

    if (formModalCliente) {
        formModalCliente.addEventListener("submit", async (e) => {
        e.preventDefault();
        const apellido = document.getElementById("modal_apellido").value.trim();
        const nombre = document.getElementById("modal_nombre").value.trim();
        const telefono = document.getElementById("modal_telefono").value.trim();
        const email = document.getElementById("modal_email").value.trim();

        try {
            const res = await fetch("../api/clientes/guardar.php", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({ nombre, apellido, telefono, email })
            });
            const data = await res.json();
            if (res.ok && data.success) {
                mostrarToast("Cliente registrado.", "exito");
                modalCliente.style.display = "none";
                formModalCliente.reset();
                await cargarSelectClientes();
            } else {
                mostrarToast(data.message || "No se pudo registrar.", "error");
            }
        } catch (err) {
            mostrarToast("Error de conexión al registrar cliente.", "error");
        }
    });
}


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
}

async function cargarServicios(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const selectEstado = document.getElementById("selectFiltroEstado");

        const termino = inputBuscador ? inputBuscador.value.trim() : "";
        const estado = selectEstado ? selectEstado.value : "";

        let url = `../api/servicios/listar.php?status=${estado}`;
        if (termino) url += `&q=${encodeURIComponent(termino)}`;

        const res = await fetch(url);
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
                    <td colspan='6' style='text-align: center; padding: 2rem;'>
                        <p style="margin-bottom: 1rem; color: #94a3b8;">No se encontraron servicios técnicos.</p>
                        <button type="button" class="secondary" onclick="limpiarYRecargar()" style="display: inline-block; width: auto; padding: 0.5rem 1.2rem;">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 servicios";
            return;
        }

        servicios.forEach(s => {
            const idServ = s.id_servicio;
            const idCliente = s.id_cliente;
            const costo = parseFloat(s.costo) || 0;
            const cliente = `${escapeHtml(s.cliente_apellido)}, ${escapeHtml(s.cliente_nombre)}`;

            let badgeColor = "#e2e8f0";
            let textColor = "#1e293b";
            const est = (s.estado || "").toLowerCase();
            
            if (est === 'completado') { badgeColor = '#bbf7d0'; textColor = '#166534'; }
            else if (est === 'en proceso') { badgeColor = '#bfdbfe'; textColor = '#1e40af'; }
            else if (est === 'pendiente') { badgeColor = '#fef08a'; textColor = '#854d0e'; }
            else if (est === 'entregado') { badgeColor = '#e9d5ff'; textColor = '#6b21a8'; }

            const badgeEstado = `<span style="background-color: ${badgeColor}; color: ${textColor}; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold; text-transform: capitalize;">${escapeHtml(s.estado)}</span>`;

            // Botón de cobro visible solo si está completado (o según prefieras)
            let btnCobrarHtml = '';
            if (est === 'completado') {
                btnCobrarHtml = `<a href="../ventas/nueva.php?id_servicio=${idServ}&id_cliente=${idCliente}&costo=${costo}&equipo=${encodeURIComponent(s.equipo_descripcion)}" class="btn-principal" style="padding: 4px 8px; font-size: 0.8rem; text-decoration: none; display: inline-block; background-color: #16a34a; margin-right: 4px;">Cobrar</a>`;
            }

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td><small>${escapeHtml(s.fecha_ingreso)}</small></td>
                <td><strong>${cliente}</strong></td>
                <td>
                    <div>${escapeHtml(s.equipo_descripcion)}</div>
                    <small style="color: #64748b;">${escapeHtml(s.diagnostico || 'Sin diagnóstico previo')}</small>
                </td>
                <td>$ ${costo.toFixed(2)}</td>
                <td>${badgeEstado}</td>
                <td class="col-acciones">
                    ${btnCobrarHtml}
                    <a href="editar.php?id=${idServ}" class="secondary btn-tabla-editar">Editar</a>
                    <button onclick="eliminarServicio(${idServ})" class="btn-tabla-eliminar">Baja</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${servicios.length} servicio(s)`;
    } catch (err) {
        mostrarToast("Error al cargar los servicios técnicos.", "error");
    }
}

async function eliminarServicio(id_servicio) {
    if (!confirm("¿Estás seguro de dar de baja este servicio técnico?")) return;
    try {
        const res = await fetch("../api/servicios/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_servicio })
        });
        const data = await res.json();
        if (data.success) {
            mostrarToast("Servicio dado de baja correctamente.", "exito");
            cargarServicios();
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


//nuevo
function inicializarNuevoServicio() {
    cargarSelectClientes();

    const btnNuevoClienteModal = document.getElementById("btnNuevoClienteModal");
    const modalCliente = document.getElementById("modalCliente");
    const btnCerrarModal = document.getElementById("btnCerrarModal");
    const formModalCliente = document.getElementById("formModalCliente");

    if (btnNuevoClienteModal && modalCliente) {
        btnNuevoClienteModal.addEventListener("click", () => {
            modalCliente.style.display = "flex";
        });
    }

    if (btnCerrarModal && modalCliente) {
        btnCerrarModal.addEventListener("click", () => {
            modalCliente.style.display = "none";
            if (formModalCliente) formModalCliente.reset();
        });
    }

    if (formModalCliente) {
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
                    modalCliente.style.display = "none";
                    formModalCliente.reset();
                    await cargarSelectClientes();
                    if (data.id_cliente) {
                        const selectCliente = document.getElementById("selectCliente");
                        if (selectCliente) selectCliente.value = data.id_cliente;
                    }
                } else {
                    mostrarToast(data.message || "No se pudo registrar.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión al registrar cliente.", "error");
            }
        });
    }

    document.getElementById("formNuevoServicio").addEventListener("submit", async (e) => {
        e.preventDefault();

        const costoVal = parseFloat(document.getElementById("costo").value);
        const datos = {
            id_cliente: document.getElementById("selectCliente").value,
            equipo_descripcion: document.getElementById("equipo_descripcion").value.trim(),
            diagnostico: document.getElementById("diagnostico").value.trim(),
            costo: Number.isNaN(costoVal) ? 0 : costoVal,
            estado: document.getElementById("estado").value
        };

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
                setTimeout(() => window.location.href = "indice.php", 1500);
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
        clientes.forEach(c => {
            select.innerHTML += `<option value="${c.id_cliente}">${c.nombre}, ${c.apellido} (${c.telefono})</option>`;
        });
    } catch (err) {
        mostrarToast("Error al cargar clientes", "error");
    }
}


//editar
function inicializarEditarServicio() {
    const id = document.getElementById("id_servicio").value;

    // Cargar datos actuales al iniciar
    (async () => {
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
    })();

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
}



   //UTILIDADES GLOBALES (Toasts y Seguridad HTML)

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