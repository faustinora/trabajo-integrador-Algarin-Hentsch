document.addEventListener("DOMContentLoaded", () => {
    const tablaClientesBody = document.getElementById("tabla-clientes-body");
    if (tablaClientesBody) {
        cargarClientes(false);

        const inputBuscador = document.getElementById("inputBuscador");
        const selectEstado = document.getElementById("selectFiltroEstado");
        const btnBuscar = document.getElementById("btnBuscar");

        if (inputBuscador) {
            inputBuscador.addEventListener("keydown", (e) => {
                if (e.key === "Enter") {
                    e.preventDefault();
                    cargarClientes(true);
                }
            });
        }

        if (btnBuscar) {
            btnBuscar.addEventListener("click", (e) => {
                e.preventDefault();
                cargarClientes(true);
            });
        }

        if (selectEstado) {
            selectEstado.addEventListener("change", () => {
                cargarClientes(false);
            });
        }

        tablaClientesBody.addEventListener("click", (e) => {
            const btnLimpiar = e.target.closest("[data-accion='limpiar-clientes']");
            if (btnLimpiar) {
                limpiarYRecargar();
                return;
            }

            const btnBaja = e.target.closest("[data-accion='baja-cliente']");
            if (btnBaja) {
                eliminarCliente(Number(btnBaja.dataset.id), btnBaja.dataset.nombre || "");
                return;
            }

            const btnHabilitar = e.target.closest("[data-accion='habilitar-cliente']");
            if (btnHabilitar) {
                habilitarCliente(Number(btnHabilitar.dataset.id), btnHabilitar.dataset.nombre || "");
            }
        });
    }

    const formNuevoCliente = document.getElementById("formNuevoCliente");
    if (formNuevoCliente) {
        formNuevoCliente.addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                nombre: document.getElementById("nombre").value.trim(),
                apellido: document.getElementById("apellido").value.trim(),
                telefono: document.getElementById("telefono").value.trim(),
                email: document.getElementById("email").value.trim()
            };

            try {
                const res = await fetch("../api/clientes/guardar.php", {
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
                    mostrarToast(resultado.message || "Error al registrar cliente.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }

    const formEditarCliente = document.getElementById("formEditarCliente");
    if (formEditarCliente) {
        inicializarEdicion();

        formEditarCliente.addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_cliente: document.getElementById("id_cliente").value,
                nombre: document.getElementById("nombre").value.trim(),
                apellido: document.getElementById("apellido").value.trim(),
                telefono: document.getElementById("telefono").value.trim(),
                email: document.getElementById("email").value.trim()
            };

            try {
                const res = await fetch("../api/clientes/actualizar.php", {
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

async function cargarClientes(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const selectEstado = document.getElementById("selectFiltroEstado");

        const termino = inputBuscador ? inputBuscador.value.trim() : "";
        const estado = selectEstado ? selectEstado.value : "1";

        const params = new URLSearchParams({ status: estado });
        if (termino) {
            params.set("q", termino);
        }

        const res = await fetch(`../api/clientes/listar.php?${params.toString()}`);
        const data = await res.json();
        const clientes = Array.isArray(data) ? data : (data.clientes || data.data || []);

        const tbody = document.getElementById("tabla-clientes-body");
        const totalElem = document.getElementById("totalRegistros");

        if (avisar) {
            if (clientes.length > 0) {
                mostrarToast(`Se encontraron ${clientes.length} resultado(s).`, "exito");
            } else {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
        }

        if (!tbody) return;
        tbody.innerHTML = "";

        if (clientes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan="5" class="celda-vacia">
                        <p class="texto-vacio">No se encontraron clientes.</p>
                        <button type="button" class="secondary btn-inline-auto" data-accion="limpiar-clientes">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 clientes";
            return;
        }

        clientes.forEach((c) => {
            const idCli = Number(c.id_cliente);
            const esActivo = Number.parseInt(c.activo, 10) === 1;
            const nombreSeguro = escapeHtml(`${c.nombre} ${c.apellido}`);

            const badgeEstado = esActivo
                ? '<span class="badge-activo">Activo</span>'
                : '<span class="badge-inactivo">Inactivo</span>';

            const accionesHtml = esActivo
                ? `<a href="editar.php?id=${idCli}" class="secondary btn-tabla-editar">Editar</a>
                   <button type="button" data-accion="baja-cliente" data-id="${idCli}" data-nombre="${nombreSeguro}" class="btn-tabla-eliminar">Dar de baja</button>`
                : `<button type="button" data-accion="habilitar-cliente" data-id="${idCli}" data-nombre="${nombreSeguro}" class="btn-habilitar">Habilitar</button>`;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${nombreSeguro}</td>
                <td>${escapeHtml(c.telefono)}</td>
                <td>${escapeHtml(c.email)}</td>
                <td>${badgeEstado}</td>
                <td class="col-acciones">${accionesHtml}</td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${clientes.length} cliente(s)`;
    } catch (err) {
        mostrarToast("Error al cargar clientes.", "error");
    }
}

async function eliminarCliente(id_cliente, nombreCompleto) {
    if (!confirm(`¿Estás seguro de dar de baja al cliente "${nombreCompleto}"?`)) return;
    try {
        const res = await fetch("../api/clientes/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_cliente })
        });
        const data = await res.json();
        if (data.success) {
            mostrarToast("Cliente dado de baja correctamente.", "exito");
            cargarClientes(false);
        } else {
            mostrarToast(data.message || "No se pudo dar de baja.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión.", "error");
    }
}

async function habilitarCliente(id_cliente, nombreCompleto) {
    if (!confirm(`¿Querés volver a habilitar al cliente "${nombreCompleto}"?`)) return;
    try {
        const res = await fetch("../api/clientes/activar.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_cliente })
        });
        const data = await res.json();
        if (data.success) {
            mostrarToast("Cliente habilitado correctamente.", "exito");
            cargarClientes(false);
        } else {
            mostrarToast(data.message || "No se pudo habilitar el cliente.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión.", "error");
    }
}

async function inicializarEdicion() {
    const idElem = document.getElementById("id_cliente");
    if (!idElem) return;
    const id = idElem.value;
    try {
        const res = await fetch("../api/clientes/listar.php?status=todos");
        const clientes = await res.json();
        const cliente = Array.isArray(clientes) ? clientes.find((c) => c.id_cliente == id) : null;

        if (cliente) {
            document.getElementById("nombre").value = cliente.nombre || "";
            document.getElementById("apellido").value = cliente.apellido || "";
            document.getElementById("telefono").value = cliente.telefono || "";
            document.getElementById("email").value = cliente.email || "";
        } else {
            mostrarToast("No se encontró el cliente.", "error");
            setTimeout(() => {
                window.location.href = "indice.php";
            }, 1500);
        }
    } catch (err) {
        mostrarToast("Error al cargar los datos del cliente.", "error");
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
    if (inputBuscador) {
        inputBuscador.value = "";
    }
    cargarClientes(false);
}

function escapeHtml(text) {
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
    return String(text || "").replace(/[&<>"']/g, (m) => map[m]);
}