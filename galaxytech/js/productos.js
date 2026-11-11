document.addEventListener("DOMContentLoaded", () => {
    cargarProductos();

    const form = document.getElementById("form-producto");
    if (form) {
        form.addEventListener("submit", async (e) => {
            e.preventDefault();
            const formData = new FormData(form);

            try {
                const res = await fetch("../api/guardar_producto.php", {
                    method: "POST",
                    body: formData
                });
                const data = await res.json();

                if (data.status === "success") {
                    alert(data.mensaje);
                    limpiarFormularioProducto();
                    cargarProductos();
                } else {
                    alert("Error: " + data.mensaje);
                }
            } catch (err) {
                console.error("Error:", err);
            }
        });
    }
});

async function cargarProductos() {
    try {
        const res = await fetch("../api/productos.php");
        const data = await res.json();

        const tbody = document.getElementById("tabla-productos-body");
        if (!tbody) return;
        tbody.innerHTML = "";

        if (data.status === "success" && data.data.length > 0) {
            data.data.forEach(prod => {
                const tr = document.createElement("tr");
                tr.innerHTML = `
                    <td>${prod.codigo}</td>
                    <td>${prod.descripcion}</td>
                    <td>$ ${parseFloat(prod.precio).toFixed(2)}</td>
                    <td>${prod.stock}</td>
                    <td>
                        <button type="button" class="btn-editar">Editar</button>
                        <button type="button" class="btn-eliminar" style="background-color: #c0392b; color: white;">Eliminar</button>
                    </td>
                `;

                
                tr.querySelector(".btn-editar").addEventListener("click", () => {
                    prepararEdicionProducto(prod.id_producto, prod.codigo, prod.descripcion, prod.precio, prod.stock);
                });

                tr.querySelector(".btn-eliminar").addEventListener("click", () => {
                    darDeBajaProducto(prod.id_producto);
                });

                tbody.appendChild(tr);
            });
        } else {
            tbody.innerHTML = "<tr><td colspan='5'>No hay productos registrados.</td></tr>";
        }
    } catch (err) {
        console.error("Error al cargar productos:", err);
    }
}

async function darDeBajaProducto(id) {
    if (!confirm("¿Confirmar baja del producto?")) return;

    const formData = new FormData();
    formData.append("id_producto", id);

    try {
        const res = await fetch("../api/baja_producto.php", {
            method: "POST",
            body: formData
        });
        const data = await res.json();

        if (data.status === "success") {
            alert(data.mensaje);
            cargarProductos();
        } else {
            alert("Error: " + data.mensaje);
        }
    } catch (err) {
        console.error("Error en baja:", err);
    }
}

function prepararEdicionProducto(id, codigo, descripcion, precio, stock) {
    document.getElementById("id_producto").value = id;
    document.getElementById("codigo").value = codigo;
    document.getElementById("descripcion").value = descripcion;
    document.getElementById("precio").value = precio;
    document.getElementById("stock").value = stock;
    document.getElementById("btn-guardar-producto").textContent = "Actualizar";
}

function limpiarFormularioProducto() {
    const form = document.getElementById("form-producto");
    if (form) form.reset();
    document.getElementById("id_producto").value = "";
    document.getElementById("btn-guardar-producto").textContent = "Guardar";
}