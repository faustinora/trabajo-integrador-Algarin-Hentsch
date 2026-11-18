document.addEventListener("DOMContentLoaded", () => {

    // Lógica para indice
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
    }

    //Lógica para form registro
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
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar cliente.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }

    // Lógica para form editar
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

// FUNCIONES LISTADO Y ACCIONES

async function cargarClientes(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const selectEstado = document.getElementById("selectFiltroEstado");
        
        const termino = inputBuscador ? inputBuscador.value.trim() : "";
        const estado = selectEstado ? selectEstado.value : "1";

        let url = `../api/clientes/listar.php?status=${estado}`;
        if (termino) {
            url += `&q=${encodeURIComponent(termino)}`;
        }

        const res = await fetch(url);
        const data = await res.json();
        const clientes = Array.isArray(data) ? data : (data.clientes || data.data || []);

        const tbody = document.getElementById("tabla-clientes-body");
        const totalElem = document.getElementById("totalRegistros");
        
        if (avisar){
            if (clientes.length > 0){
                mostrarToast(`Se encontraron ${clientes.length} resultado(s).`, "exito");
            } else{
                mostrarToast("No se encontraron coincidencias.", "error");
            }
        }
                
        if (!tbody) return;
        
        tbody.innerHTML = "";

        if (clientes.length === 0) {
            tbody.innerHTML = `
                <tr>
                    <td colspan='5' style='text-align: center; padding: 2rem;'>
                        <p style="margin-bottom: 1rem; color: #94a3b8;">No se encontraron clientes.</p>
                        <button type="button" class="secondary" onclick="limpiarYRecargar()" style="display: inline-block; width: auto; padding: 0.5rem 1.2rem;">Actualizar</button>
                    </td>
                </tr>`;
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 clientes";
            return;
        }

        clientes.forEach(c => {
            const idCli = c.id_cliente;
            const esActivo = parseInt(c.activo) === 1;
            const nombreCompleto = `${escapeHtml(c.nombre)} ${escapeHtml(c.apellido)}`;

            const badgeEstado = esActivo 
                ? '<span style="background-color: #bbf7d0; color: #166534; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Activo</span>'
                : '<span style="background-color: #fecaca; color: #991b1b; padding: 3px 8px; border-radius: 4px; font-size: 0.8rem; font-weight: bold;">Inactivo</span>';

            const accionesHtml = esActivo
                ? `<a href="editar.php?id=${idCli}" class="secondary btn-tabla-editar">Editar</a>
                   <button onclick="eliminarCliente(${idCli}, '${nombreCompleto}')" class="btn-tabla-eliminar">Dar de baja</button>`
                : `<button onclick="habilitarCliente(${idCli}, '${nombreCompleto}')" style="background-color: #16a34a; border-color: #16a34a; color: white; padding: 4px 10px; font-size: 0.8rem; border-radius: 4px; cursor: pointer; margin: 0;">Habilitar</button>`;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${nombreCompleto}</td>
                <td>${escapeHtml(c.telefono)}</td>
                <td>${escapeHtml(c.email)}</td>
                <td>${badgeEstado}</td>
                <td class="col-acciones">
                    ${accionesHtml}
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${clientes.length} cliente(s)`;
    } catch (err) {
        console.error("Error al cargar clientes:", err);
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
            cargarClientes();
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
            cargarClientes();
        } else {
            mostrarToast(data.message || "No se pudo habilitar el cliente.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión.", "error");
    }
}

// FUNCIONES EDICIÓN

async function inicializarEdicion() {
    const idElem = document.getElementById("id_cliente");
    if (!idElem) return;
    const id = idElem.value;
    try {
        const res = await fetch(`../api/clientes/listar.php?status=todos`);
        const clientes = await res.json();
        const cliente = clientes.find(c => c.id_cliente == id);

        if (cliente) {
            document.getElementById("nombre").value = cliente.nombre;
            document.getElementById("apellido").value = cliente.apellido;
            document.getElementById("telefono").value = cliente.telefono;
            document.getElementById("email").value = cliente.email || '';
        } else {
            mostrarToast("No se encontró el cliente.", "error");
            setTimeout(() => window.location.href = "indice.php", 1500);
        }
    } catch (err) {
        mostrarToast("Error al cargar los datos del cliente.", "error");
    }
}

// auxiliares

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
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text || '').replace(/[&<>"']/g, m => map[m]);
}