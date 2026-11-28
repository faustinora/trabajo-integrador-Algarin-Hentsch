let productosStockBajo = [];

document.addEventListener("DOMContentLoaded", () => {
    cargarDashboard();

    const cardStock = document.getElementById("card-stock-bajo");
    const modalStock = document.getElementById("modal-stock-bajo");
    const btnCerrar = document.getElementById("cerrar-modal-stock");

    if (cardStock && modalStock) {
        cardStock.addEventListener("click", () => {
            renderizarModalStock();
            modalStock.showModal();
        });
    }

    if (btnCerrar && modalStock) {
        btnCerrar.addEventListener("click", () => {
            modalStock.close();
        });
    }

    if (modalStock) {
        modalStock.addEventListener("click", (e) => {
            if (e.target === modalStock) {
                modalStock.close();
            }
        });
    }
});

async function cargarDashboard() {
    const formatoMoneda = new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
        maximumFractionDigits: 0
    });

    try {
        const res = await fetch("../api/informes/datos.php");
        const data = await res.json();

        if (data.status === "success") {
            document.getElementById("kpi-ventas").textContent = formatoMoneda.format(data.kpis.monto_ventas || 0);
            document.getElementById("kpi-pendientes").textContent = data.kpis.servicios_pendientes ?? 0;
            document.getElementById("kpi-stock-bajo").textContent = data.kpis.stock_bajo ?? 0;
            document.getElementById("kpi-clientes").textContent = data.kpis.clientes ?? 0;

            productosStockBajo = data.lista_stock_bajo || [];
            renderizarProductos(data.informe_productos || []);
            renderizarClientes(data.informe_clientes || []);
        }
    } catch (err) {
        // Error controlado en interfaz o consola si se requiere
    }
}

function renderizarModalStock() {
    const tbody = document.getElementById("tabla-modal-stock");
    if (!tbody) return;
    tbody.innerHTML = "";

    if (productosStockBajo.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" class="celda-centrada texto-vacio">No hay productos.</td></tr>`;
        return;
    }

    productosStockBajo.forEach((prod) => {
        tbody.innerHTML += `
            <tr>
                <td><code>${escapeHtml(prod.codigo)}</code></td>
                <td>${escapeHtml(prod.nombre)}</td>
                <td class="celda-derecha texto-peligro"><strong>${escapeHtml(prod.stock)}</strong></td>
            </tr>`;
    });
}

function renderizarProductos(productos) {
    const tbody = document.getElementById("tabla-inf-productos");
    if (!tbody) return;
    tbody.innerHTML = "";

    if (productos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" class="celda-centrada texto-vacio">Aún no hay ventas registradas.</td></tr>`;
        return;
    }

    productos.forEach((item) => {
        tbody.innerHTML += `
            <tr>
                <td>${escapeHtml(item.nombre)}</td>
                <td class="celda-derecha"><strong>${escapeHtml(item.total_vendido)}</strong></td>
            </tr>`;
    });
}

function renderizarClientes(clientes) {
    const tbody = document.getElementById("tabla-inf-clientes");
    if (!tbody) return;
    tbody.innerHTML = "";

    if (clientes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="celda-centrada texto-vacio">Sin actividad de clientes registrada.</td></tr>`;
        return;
    }

    clientes.forEach((item) => {
        tbody.innerHTML += `
            <tr>
                <td><strong>${escapeHtml(item.apellido)}, ${escapeHtml(item.nombre)}</strong></td>
                <td class="celda-centrada">${escapeHtml(item.total_ventas)}</td>
                <td class="celda-centrada">${escapeHtml(item.total_servicios)}</td>
                <td class="celda-derecha"><strong>${escapeHtml(item.total_operaciones)}</strong></td>
            </tr>`;
    });
}

function escapeHtml(text) {
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
    return String(text || "").replace(/[&<>"']/g, (m) => map[m]);
}