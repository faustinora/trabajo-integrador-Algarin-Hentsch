document.addEventListener("DOMContentLoaded", () => {
    // Si estás en la vista de listado de usuarios, cargamos los datos al iniciar
    const tablaUsuarios = document.getElementById("tabla-usuarios-body");
    if (tablaUsuarios) {
        cargarUsuarios();
    }

    // Manejo del formulario de creación de usuario (si existe en la vista)
    const formNuevoUsuario = document.getElementById("form-nuevo-usuario");
    if (formNuevoUsuario) {
        formNuevoUsuario.addEventListener("submit", async (e) => {
            e.preventDefault();
            await registrarUsuario(formNuevoUsuario);
        });
    }
});

// Función para obtener y listar los usuarios desde la API
async function cargarUsuarios() {
    try {
        const response = await fetch("../api/usuarios/listar.php"); // Ajustá la ruta según tu endpoint de API
        if (!response.ok) throw new Error("Error al obtener los usuarios.");

        const usuarios = await response.json();
        const tablaUsuarios = document.getElementById("tabla-usuarios-body");
        tablaUsuarios.innerHTML = "";

        if (usuarios.length === 0) {
            tablaUsuarios.innerHTML = `<tr><td colspan="5" style="text-align: center;">No hay usuarios registrados.</td></tr>`;
            return;
        }

        usuarios.forEach(user => {
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${user.id_usuario}</td>
                <td>${escapeHtml(user.usuario)}</td>
                <td>${escapeHtml(user.rol)}</td>
                <td>${user.activo == 1 ? 'Activo' : 'Inactivo'}</td>
                <td>
                    <a href="editar.php?id=${user.id_usuario}" class="btn-editar">Editar</a>
                    <button onclick="cambiarEstadoUsuario(${user.id_usuario}, ${user.activo})" class="btn-desactivar">
                        ${user.activo == 1 ? 'Desactivar' : 'Activar'}
                    </button>
                </td>
            `;
            tablaUsuarios.appendChild(tr);
        });
    } catch (error) {
        console.error("Error:", error);
        alert("Hubo un error al cargar la lista de usuarios.");
    }
}

// Función para registrar un nuevo usuario mediante Fetch (POST)
async function registrarUsuario(form) {
    const formData = new FormData(form);

    try {
        const response = await fetch("../api/usuarios/crear.php", {
            method: "POST",
            body: formData
        });

        const resultado = await response.json();

        if (resultado.success) {
            alert("Usuario creado exitosamente.");
            window.location.href = "indice.php";
        } else {
            alert("Error: " + (resultado.message || "No se pudo registrar el usuario."));
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Ocurrió un error en la comunicación con el servidor.");
    }
}

// Función para hacer baja lógica asíncrona (o cambiar estado)
async function cambiarEstadoUsuario(id_usuario, estadoActual) {
    const nuevoEstado = estadoActual == 1 ? 0 : 1;
    const accionTexto = nuevoEstado == 1 ? "activar" : "desactivar";

    if (!confirm(`¿Estás seguro de que querés ${accionTexto} este usuario?`)) return;

    try {
        const response = await fetch("../api/usuarios/cambiar_estado.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ id_usuario: id_usuario, activo: nuevoEstado })
        });

        const resultado = await response.json();

        if (resultado.success) {
            cargarUsuarios(); // Recargamos la tabla dinámicamente
        } else {
            alert("Error al cambiar el estado: " + resultado.message);
        }
    } catch (error) {
        console.error("Error:", error);
        alert("Error de conexión con el servidor.");
    }
}

// Utilidad auxiliar para prevenir XSS al inyectar HTML desde JS
function escapeHtml(text) {
    const map = {
        '&': '&amp;',
        '<': '&lt;',
        '>': '&gt;',
        '"': '&quot;',
        "'": '&#039;'
    };
    return text.toString().replace(/[&<>"']/g, function(m) { return map[m]; });
}