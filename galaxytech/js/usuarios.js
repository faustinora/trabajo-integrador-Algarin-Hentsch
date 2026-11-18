document.addEventListener("DOMContentLoaded", () => {
    // Vista Lista
    if (document.getElementById("tabla-usuarios-body")) {
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
    }

    // Vista Nuevo
    const formNuevoUsuario = document.getElementById("formNuevoUsuario");
    if (formNuevoUsuario) {
        formNuevoUsuario.addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                usuario: document.getElementById("usuario").value.trim(),
                clave: document.getElementById("clave").value.trim(),
                rol: document.getElementById("rol").value
            };

            try {
                const res = await fetch("../api/usuarios/guardar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message || "Usuario registrado con éxito.", "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar usuario.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }

    // Vista Editar
    const formEditarUsuario = document.getElementById("formEditarUsuario");
    if (formEditarUsuario) {
        cargarDatosEdicion();

        formEditarUsuario.addEventListener("submit", async (e) => {
            e.preventDefault();

            const datos = {
                id_usuario: document.getElementById("id_usuario").value,
                usuario: document.getElementById("usuario").value.trim(),
                clave: document.getElementById("clave").value.trim(),
                rol: document.getElementById("rol").value,
                activo: parseInt(document.getElementById("activo").value)
            };

            try {
                const res = await fetch("../api/usuarios/actualizar.php", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(datos)
                });
                const resultado = await res.json();

                if (res.ok && resultado.success) {
                    mostrarToast(resultado.message || "Usuario actualizado correctamente.", "exito");
                    setTimeout(() => window.location.href = "indice.php", 1500);
                } else {
                    mostrarToast(resultado.message || "Error al actualizar usuario.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }
});

// Funciones Auxiliares

async function cargarUsuarios(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const filtroEstado =document.getElementById("filtroEstado");
        
        const termino = inputBuscador ? inputBuscador.value.trim() : "";
        const estado = filtroEstado ? filtroEstado.value : "1";

        let url = `../api/usuarios/listar.php?status=${estado}`;
        if(termino){
            url += `&q=${encodeURIComponent(termino)}`;
        }
        
        const res = await fetch(url);
        const usuarios = await res.json();
        
        const tbody = document.getElementById("tabla-usuarios-body");
        const totalElem = document.getElementById("totalRegistros");

        if (!tbody) return;
        tbody.innerHTML = "";

        if (!Array.isArray(usuarios) || usuarios.length === 0) {
            if (avisar) {
                mostrarToast("No se encontraron coincidencias.", "error");
            }
            
            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td colspan='4' style='text-align: center; padding: 2rem;'>
                    <p style="margin-bottom: 1rem; color: #94a3b8;">No se encontraron usuarios.</p>
                    <button type="button" class="secondary" onclick="limpiarYRecargar()" style="display: inline-block; width: auto; padding: 0.5rem 1.2rem;">Actualizar</button>
                </td>`;
            tbody.appendChild(tr);

            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 usuarios";
            return;
        }

        if (avisar) {
            mostrarToast(`Se encontraron ${usuarios.length} resultado(s).`, "exito");
        }

        usuarios.forEach(u => {
            const idUsr = u.id_usuario;
            const esActivo = parseInt(u.activo) === 1;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${escapeHtml(u.usuario)}</td>
                <td>${escapeHtml(u.rol)}</td>
                <td><span style="color: ${esActivo ? '#22c55e' : '#ef4444'}; font-weight: bold;">${esActivo ? 'Activo' : 'Inactivo'}</span></td>
                <td class="col-acciones">
                <a href="editar.php?id=${idUsr}" class="secondary btn-tabla-editar">Editar</a>
                    ${ 
                        esActivo 
                        ? `<button onclick="cambiarEstadoUsuario(${idUsr}, '${escapeHtml(u.usuario)}', 'baja')" class="btn-tabla-eliminar" style="background-color: #ef4444; color: white;">Eliminar</button>` 
                        : `<button onclick="cambiarEstadoUsuario(${idUsr}, '${escapeHtml(u.usuario)}', 'habilitar')" class="btn-tabla-habilitar" style="background-color: #22c55e; color: white;">Habilitar</button>`
                    }
                </td>
            `;
            tbody.appendChild(tr);
        });

        if (totalElem) totalElem.innerHTML = `<strong>Total:</strong> ${usuarios.length} usuario(s)`;
    } catch (err) {
        mostrarToast("Error al cargar usuarios.", "error");
    }
}

async function cambiarEstadoUsuario(id_usuario, nombre, accion) {
    const endpoint = accion === 'habilitar' ? '../api/usuarios/habilitar.php' : '../api/usuarios/baja.php';
    const textoAccion = accion === 'habilitar' ? 'habilitar' : 'dar de baja';
    
    if (!confirm(`¿Estás seguro de ${accionTexto} al usuario "${nombre}"?`)) return;

    try {
        const res = await fetch(endpoint, {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_usuario })
        });
        const data = await res.json();
        
        if (res.ok && data.success) {
            mostrarToast(data.message, "exito");
            cargarUsuarios(false);
        } else {
            mostrarToast(data.message || "Error al actualizar el estado.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión con el servidor.", "error");
    }
}

async function cargarDatosEdicion() {
    const id = document.getElementById("id_usuario").value;
    try {
        const res = await fetch("../api/usuarios/listar.php");
        const data = await res.json();
        const usuarios = Array.isArray(data) ? data : (data.usuarios || data.data || []);
        const user = usuarios.find(u => u.id_usuario == id);

        if (user) {
            document.getElementById("usuario").value = user.usuario;
            document.getElementById("rol").value = user.rol;
            document.getElementById("activo").value = user.activo;
        } else {
            mostrarToast("No se encontró el usuario.", "error");
            setTimeout(() => window.location.href = "indice.php", 1500);
        }
    } catch (err) {
        mostrarToast("Error al cargar los datos del usuario.", "error");
    }
}

async function eliminarUsuario(id_usuario, nombre) {
    if (!confirm(`¿Dar de baja al usuario "${nombre}"?`)) return;
    try {
        const res = await fetch("../api/usuarios/baja.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id_usuario, activo: 0 })
        });
        const data = await res.json();
        if (data.success || data.status == "success") {
            mostrarToast("Usuario dado de baja.", "exito");
            cargarUsuarios(false);
        } else {
            mostrarToast(data.message || "Error al eliminar.", "error");
        }
    } catch (err) {
        mostrarToast("Error de conexión.", "error");
    }
}

function limpiarYRecargar(){
    const inputBuscador = document.getElementById("inputBuscador");
    if (inputBuscador){
        inputBuscador.value = "";
    }
    cargarUsuarios(false);
}

// Utilidades Generales (Toast y XSS)

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