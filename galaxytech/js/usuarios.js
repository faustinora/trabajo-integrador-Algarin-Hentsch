document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios(false);

    const inputBuscador = document.getElementById("inputBuscador");
    const btnBuscar = document.getElementById("btnBuscar");

    if (inputBuscador) {
        inputBuscador.addEventListener("keydown", (e) => {
            if (e.key === "Enter") {
                e.preventDefault();
                cargarUsuarios(true);
            }
        });
    }

    if (btnBuscar) {
        btnBuscar.addEventListener("click", (e) => {
            e.preventDefault();
            cargarUsuarios(true);
        });
    }
});

async function cargarUsuarios(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const termino = inputBuscador ? inputBuscador.value.trim() : "";

        const url = termino ? `../api/usuarios/listar.php?q=${encodeURIComponent(termino)}` : "../api/usuarios/listar.php";
        const res = await fetch(url);
        const data = await res.json();
        const usuarios = Array.isArray(data) ? data : (data.usuarios || data.data || []);

        const tbody = document.getElementById("tabla-usuarios-body");
        const totalElem = document.getElementById("totalRegistros");

        if (avisar) {
            if (usuarios.length > 0) {
                mostrarToast(`Se encontraron ${usuarios.length} resultado(s).`, "exito");
            } else {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
        }

        if (!tbody) return;
        tbody.innerHTML = "";

        if (usuarios.length === 0) {
            tbody.innerHTML = "<tr><td colspan='3' style='text-align: center;'>No se encontraron usuarios.</td></tr>";
            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 usuarios";
            return;
        }

        usuarios.forEach(u => {
            const idUsr = u.id_usuario || u.id;
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${escapeHtml(u.usuario || u.nombre_usuario)}</td>
                <td>${escapeHtml(u.rol)}</td>
                <td class="col-acciones">
                    <a href="editar.php?id=${idUsr}" class="secondary btn-tabla-editar">Editar</a>
                    <button onclick="eliminarUsuario(${idUsr}, '${escapeHtml(u.usuario || u.nombre_usuario)}')" class="btn-tabla-eliminar">Eliminar</button>
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${usuarios.length} usuario(s)`;
    } catch (err) {
        mostrarToast("Error al cargar usuarios.", "error");
    }
}

async function eliminarUsuario(id_usuario, nombre) {
    if (!confirm(`¿Dar de baja al usuario "${nombre}"?`)) return;
    try {
        const res = await fetch("../api/usuarios/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_usuario })
        });
        const data = await res.json();
        if (data.success || data.status === "success") {
            mostrarToast("Usuario dado de baja.", "exito");
            cargarUsuarios(false);
        } else {
            mostrarToast(data.message || "Error al eliminar.", "error");
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