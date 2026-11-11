document.addEventListener("DOMContentLoaded", () => {
    cargarVentas();

    const form = document.getElementById("form-venta");
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const res = await fetch("../api/guardar_venta.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();

                if (data.status === "success") {
                    alert(data.mensaje);
                    form.reset();
                    cargarVentas();
                } else {
                    alert("Error: " + data.mensaje);
                }
            } catch (err) {
                console.error("Error:", err);
            }
        });
    }
});

async function cargarVentas() {
    try {
        const res = await fetch("../api/ventas.php");
        const data = await res.json();

        const tbody = document.getElementById("tabla-ventas-body");
        if (!tbody) return;
        tbody.innerHTML = "";

        if (data.status === "success" && data.data.length > 0) {
            data.data.forEach(venta => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>#${venta.id_venta}</td>
                    <td>${venta.apellido}, ${venta.nombre}</td>
                    <td>$ ${parseFloat(venta.total).toFixed(2)}</td>
                    <td>${venta.fecha}</td>
                `;
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='4'>No hay ventas registradas.</td></tr>";
        }
    } catch (err) {
        console.error("Error al cargar ventas:", err);
    }
}