document.addEventListener("DOMContentLoaded", () => {
    cargarServicios(false);

    const inputBuscador = document.getElementById("inputBuscador");
    const selectEstado = document.getElementById("selectFiltroEstado");
    const btnBuscar = document.getElementById("btnBuscar");

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
});

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
            tbody.innerHTML = "<tr><td colspan='6' style='text-align: center;'>No se encontraron servicios técnicos.</td></tr>";
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 servicios";
            return;
        }

        servicios.forEach(s => {
            const idServ = s.id_servicio;
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