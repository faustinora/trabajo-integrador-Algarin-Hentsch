document.addEventListener("DOMContentLoaded", () => {
    cargarServicios();

    const form = document.getElementById("form-servicio");
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const res = await fetch("../api/guardar_servicio.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();

                if (data.status === "success") {
                    alert(data.mensaje);
                    limpiarFormularioServicio();
                    cargarServicios();
                } else {
                    alert("Error: " + data.mensaje);
                }
            } catch (err) {
                console.error("Error:", err);
            }
        });
    }
});

async function cargarServicios() {
    try {
        const res = await fetch("../api/servicios.php");
        const data = await res.json();

        const tbody = document.getElementById("tabla-servicios-body");
        if (!tbody) return;
        tbody.innerHTML = "";

        if (data.status === "success" && data.data.length > 0) {
            data.data.forEach(serv => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${serv.apellido}, ${serv.nombre}</td>
                    <td>${serv.equipo}</td>
                    <td>${serv.descripcion}</td>
                    <td><strong>${serv.estado}</strong></td>
                    <td>${serv.fecha}</td>
                    <td>
                        <button type="button" class="btn-editar">Editar</button>
                        <button type="button" class="btn-eliminar" style="background-color: #c0392b; color: white;">Eliminar</button>
                    </td>
                `;

                
                tr.querySelector(".btn-editar").addEventListener("click", () => {
                    prepararEdicionServicio(serv.id_servicio, serv.id_cliente, serv.equipo, serv.descripcion, serv.estado);
                });

                tr.querySelector(".btn-eliminar").addEventListener("click", () => {
                    darDeBajaServicio(serv.id_servicio);
                });

                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='6'>No hay servicios registrados.</td></tr>";
        }
    } catch (err) {
        console.error("Error al cargar:", err);
    }
}

async function darDeBajaServicio(id) {
    if (!confirm("¿Confirmar baja del servicio?")) return;

    const formData = new FormData();
    formData.append("id_servicio", id);

    try {
        const res = await fetch("../api/baja_servicio.php", {
            method: "POST",
            body: formData
        });
        const data = await res.json();

        if (data.status === "success") {
            alert(data.mensaje);
            cargarServicios();
        } else {
            alert("Error: " + data.mensaje);
        }
    } catch (err) {
        console.error("Error en baja:", err);
    }
}

function prepararEdicionServicio(id, id_cliente, equipo, descripcion, estado) {
    document.getElementById("id_servicio").value = id;
    document.getElementById("id_cliente").value = id_cliente;
    document.getElementById("equipo").value = equipo;
    document.getElementById("descripcion").value = descripcion;
    document.getElementById("estado").value = estado;
    document.getElementById("btn-guardar-servicio").textContent = "Actualizar";
}

function limpiarFormularioServicio() {
    const form = document.getElementById("form-servicio");
    if (form) form.reset();
    document.getElementById("id_servicio").value = "";
    document.getElementById("btn-guardar-servicio").textContent = "Guardar";
}