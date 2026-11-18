document.addEventListener("DOMContentLoaded", () => {
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
});

async function cargarVentas(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const termino = inputBuscador ? inputBuscador.value.trim() : "";

        const url = termino ? `../api/ventas/listar.php?q=${encodeURIComponent(termino)}` : "../api/ventas/listar.php";
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
            tbody.innerHTML = "<tr><td colspan='5' style='text-align: center;'>No hay ventas registradas.</td></tr>";
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 ventas";
            if (spanTotalVentas) spanTotalVentas.textContent = "$0.00";
            return;
        }

        let recaudacionTotal = 0;

        ventas.forEach(v => {
            const idVenta = v.id_venta;
            const totalNum = parseFloat(v.total || 0);
            recaudacionTotal += totalNum;

            const clienteNombre = v.apellido ? `${escapeHtml(v.apellido)}, ${escapeHtml(v.nombre)}` : 'Consumidor Final';
            const vendedorNombre = escapeHtml(v.vendedor || 'Sistema');

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${escapeHtml(v.fecha_venta)}</td>
                <td><strong>${clienteNombre}</strong></td>
                <td><span style="color: #64748b; font-size: 0.9rem;">${vendedorNombre}</span></td>
                <td>$ ${totalNum.toFixed(2)}</td>
                <td class="col-acciones">
                    <button onclick="anularVenta(${idVenta})" class="btn-tabla-eliminar">Anular</button>
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