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

    // Cerrar modal haciendo clic fuera del cuadro
    if (modalStock) {
        modalStock.addEventListener("click", (e) => {
            if (e.target === modalStock) {
                modalStock.close();
            }
        });
    }
});

async function cargarDashboard() {
    const formatoMoneda = new Intl.NumberFormat('es-AR', {
        style: 'currency',
        currency: 'ARS',
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

            // Guardar lista para el modal de stock bajo
            productosStockBajo = data.lista_stock_bajo || [];

            // 2. Cargar Tabla: Productos más vendidos
            renderizarProductos(data.informe_productos || []);

            // 3. Cargar Tabla: Clientes con mayor actividad
            renderizarClientes(data.informe_clientes || []);
        } else {
            console.error("Error en la respuesta de la API:", data.mensaje);
        }
    } catch (err) {
        console.error("Error de conexión al cargar informes:", err);
    }
}

function renderizarModalStock() {
    const tbody = document.getElementById("tabla-modal-stock");
    tbody.innerHTML = "";

    if (productosStockBajo.length === 0) {
        tbody.innerHTML = `<tr><td colspan="3" style="text-align:center; color:#94a3b8;">No hay productos.</td></tr>`;
        return;
    }

    productosStockBajo.forEach(prod => {
        tbody.innerHTML += `
            <tr>
                <td><code>${escapeHtml(prod.codigo)}</code></td>
                <td>${escapeHtml(prod.nombre)}</td>
                <td style="text-align: right; color: #ef4444;"><strong>${prod.stock}</strong></td>
            </tr>`;
    });
}

function renderizarProductos(productos) {
    const tbody = document.getElementById("tabla-inf-productos");
    tbody.innerHTML = "";

    if (productos.length === 0) {
        tbody.innerHTML = `<tr><td colspan="2" style="text-align:center; color:#94a3b8;">Aún no hay ventas registradas.</td></tr>`;
        return;
    }

    productos.forEach(item => {
        tbody.innerHTML += `
            <tr>
                <td>${escapeHtml(item.nombre)}</td>
                <td style="text-align: right;"><strong>${item.total_vendido}</strong></td>
            </tr>`;
    });
}

function renderizarClientes(clientes) {
    const tbody = document.getElementById("tabla-inf-clientes");
    tbody.innerHTML = "";

    if (clientes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" style="text-align:center; color:#94a3b8;">Sin actividad de clientes registrada.</td></tr>`;
        return;
    }

    clientes.forEach(item => {
        tbody.innerHTML += `
            <tr>
                <td><strong>${escapeHtml(item.apellido)}, ${escapeHtml(item.nombre)}</strong></td>
                <td style="text-align: center;">${item.total_ventas}</td>
                <td style="text-align: center;">${item.total_servicios}</td>
                <td style="text-align: right;"><strong>${item.total_operaciones}</strong></td>
            </tr>`;
    });
}

function escapeHtml(text) {
    const map = { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#039;' };
    return String(text || '').replace(/[&<>"']/g, m => map[m]);
}