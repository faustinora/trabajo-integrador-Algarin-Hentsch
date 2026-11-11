document.addEventListener("DOMContentLoaded", () => {
    cargarClientes();

    const form = document.getElementById("form-cliente");
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const res = await fetch("../api/guardar_cliente.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();

                if (data.status === "success") {
                    alert(data.mensaje);
                    limpiarFormulario();
                    cargarClientes();
                } else {
                    alert("Error: " + data.mensaje);
                }
            } catch (err) {
                console.error("Error:", err);
            }
        });
    }
});

async function cargarClientes() {
    try {
        const res = await fetch("../api/cliente.php");
        const data = await res.json();

        const tbody = document.getElementById("tabla-clientes-body");
        if (!tbody) return;
        tbody.innerHTML = "";

        if (data.status === "success" && data.data.length > 0) {
            data.data.forEach(cli => {
                const tr = document.createElement("tr");
                
                tr.innerHTML = `
                    <td>${cli.nombre}</td>
                    <td>${cli.apellido}</td>
                    <td>${cli.telefono}</td>
                    <td>${cli.email}</td>
                    <td>
                        <button type="button" class="btn-editar">Editar</button>
                        <button type="button" class="btn-eliminar" style="background-color: #c0392b; color: white;">Eliminar</button>
                    </td>
                `;

                tr.querySelector(".btn-editar").addEventListener("click", () => {
                    document.getElementById("id_cliente").value = cli.id_cliente;
                    document.getElementById("nombre").value = cli.nombre;
                    document.getElementById("apellido").value = cli.apellido;
                    document.getElementById("telefono").value = cli.telefono;
                    document.getElementById("email").value = cli.email;
                    document.getElementById("btn-guardar").textContent = "Actualizar";
                });
                tr.querySelector(".btn-eliminar").addEventListener("click", () =>{
                    darDeBaja(cli.id_cliente);
                });
                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='4'>No hay clientes registrados.</td></tr>";
        }
    } catch (err) {
        console.error("Error al cargar:", err);
    }
}

function prepararEdicion(id, nombre, apellido, telefono, email) {
    document.getElementById("id_cliente").value = id;
    document.getElementById("nombre").value = nombre;
    document.getElementById("apellido").value = apellido;
    document.getElementById("telefono").value = telefono;
    document.getElementById("email").value = email;
    
    document.getElementById("btn-guardar").textContent = "Actualizar";
}

function limpiarFormulario(){
    const form = document.getElementById("form-cliente");
    if (form) form.reset();
    document.getElementById("id_cliente").value = "";
    document.getElementById("btn-guardar").textContent = "Guardar";
}

async function darDeBaja(id) {
    if (!confirm("¿Confirmar baja del cliente?")) return;

    const formData = new FormData();
    formData.append("id_cliente", id);

    try {
        const res = await fetch("../api/baja_cliente.php", {
            method: "POST",
            body: formData
        });
        const data = await res.json();

        if (data.status === "success") {
            alert(data.mensaje);
            cargarClientes();
        } else {
            alert("Error: " + data.mensaje);
        }
    } catch (err) {
        console.error("Error:", err);
    }
}