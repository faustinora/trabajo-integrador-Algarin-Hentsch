document.addEventListener("DOMContentLoaded", () => {
    cargarClientes(false);

    const inputBuscador = document.getElementById("inputBuscador");
    const selectEstado = document.getElementById("selectFiltroEstado");
    const btnBuscar = document.getElementById("btnBuscar");

    if (inputBuscador) {
        inputBuscador.addEventListener("keydown", (e) => {
            if(e.key === "Enter"){
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

});

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
            tbody.innerHTML = "<tr><td colspan='5' style='text-align: center;'>No se encontraron clientes.</td></tr>";
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