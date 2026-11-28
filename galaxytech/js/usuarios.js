document.addEventListener("DOMContentLoaded", () => {
    const tbody = document.getElementById("tabla-usuarios-body");
    if (tbody) {
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

        tbody.addEventListener("click", (e) => {
            const btnLimpiar = e.target.closest("[data-accion='limpiar-usuarios']");
            if (btnLimpiar) {
                limpiarYRecargar();
                return;
            }

            const btnEstado = e.target.closest("[data-accion='cambiar-estado-usuario']");
            if (btnEstado) {
                cambiarEstadoUsuario(
                    Number(btnEstado.dataset.id),
                    btnEstado.dataset.usuario || "",
                    btnEstado.dataset.estadoAccion || "baja"
                );
            }
        });
    }

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
                    setTimeout(() => {
                        window.location.href = "indice.php";
                    }, 1500);
                } else {
                    mostrarToast(resultado.message || "Error al registrar usuario.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }

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
                activo: Number.parseInt(document.getElementById("activo").value, 10)
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
                    setTimeout(() => {
                        window.location.href = "indice.php";
                    }, 1500);
                } else {
                    mostrarToast(resultado.message || "Error al actualizar usuario.", "error");
                }
            } catch (err) {
                mostrarToast("Error de conexión con el servidor.", "error");
            }
        });
    }
});

async function cargarUsuarios(avisar = false) {
    try {
        const inputBuscador = document.getElementById("inputBuscador");
        const filtroEstado = document.getElementById("filtroEstado");

        const termino = inputBuscador ? inputBuscador.value.trim() : "";
        const estado = filtroEstado ? filtroEstado.value : "1";

        const params = new URLSearchParams({ status: estado });
        if (termino) {
            params.set("q", termino);
        }

        const res = await fetch(`../api/usuarios/listar.php?${params.toString()}`);
        const data = await res.json();
        const usuarios = Array.isArray(data) ? data : (data.usuarios || data.data || []);

        const tbody = document.getElementById("tabla-usuarios-body");
        const totalElem = document.getElementById("totalRegistros");

        if (!tbody) return;
        tbody.innerHTML = "";

        if (usuarios.length === 0) {
            if (avisar) {
                mostrarToast("No se encontraron coincidencias.", "error");
            }

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td colspan="4" class="celda-vacia">
                    <p class="texto-vacio">No se encontraron usuarios.</p>
                    <button type="button" class="secondary btn-inline-auto" data-accion="limpiar-usuarios">Actualizar</button>
                </td>`;
            tbody.appendChild(tr);

            if (totalElem) totalElem.innerHTML = "<strong>Total:</strong> 0 usuarios";
            return;
        }

        if (avisar) {
            mostrarToast(`Se encontraron ${usuarios.length} resultado(s).`, "exito");
        }

        usuarios.forEach((u) => {
            const idUsr = Number(u.id_usuario);
            const esActivo = Number.parseInt(u.activo, 10) === 1;
            const usuarioSeguro = escapeHtml(u.usuario);
            const estadoClase = esActivo ? "estado-texto-activo" : "estado-texto-inactivo";
            const estadoTexto = esActivo ? "Activo" : "Inactivo";

            const botonAccion = esActivo
                ? `<button type="button" data-accion="cambiar-estado-usuario" data-id="${idUsr}" data-usuario="${usuarioSeguro}" data-estado-accion="baja" class="btn-tabla-eliminar">Eliminar</button>`
                : `<button type="button" data-accion="cambiar-estado-usuario" data-id="${idUsr}" data-usuario="${usuarioSeguro}" data-estado-accion="habilitar" class="btn-habilitar">Habilitar</button>`;

            const tr = document.createElement("tr");
            tr.innerHTML = `
                <td>${usuarioSeguro}</td>
                <td>${escapeHtml(u.rol)}</td>
                <td><span class="${estadoClase}">${estadoTexto}</span></td>
                <td class="col-acciones">
                    <a href="editar.php?id=${idUsr}" class="secondary btn-tabla-editar">Editar</a>
                    ${botonAccion}
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
    const endpoint = accion === "habilitar" ? "../api/usuarios/habilitar.php" : "../api/usuarios/baja.php";
    const textoAccion = accion === "habilitar" ? "habilitar" : "dar de baja";

    if (!confirm(`¿Estás seguro de ${textoAccion} al usuario "${nombre}"?`)) return;

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
        const user = usuarios.find((u) => u.id_usuario == id);

        if (user) {
            document.getElementById("usuario").value = user.usuario || "";
            document.getElementById("rol").value = user.rol || "";
            document.getElementById("activo").value = user.activo ?? 1;
        } else {
            mostrarToast("No se encontró el usuario.", "error");
            setTimeout(() => {
                window.location.href = "indice.php";
            }, 1500);
        }
    } catch (err) {
        mostrarToast("Error al cargar los datos del usuario.", "error");
    }
}

function limpiarYRecargar() {
    const inputBuscador = document.getElementById("inputBuscador");
    if (inputBuscador) {
        inputBuscador.value = "";
    }
    cargarUsuarios(false);
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
    const map = { "&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#039;" };
    return String(text || "").replace(/[&<>"']/g, (m) => map[m]);
}