<?php
session_start();
if (!isset($_SESSION['usuario'])) {
    header("Location: ../login.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Nueva Venta - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    
    <main class="container contenedor-principal" id="mainNuevaVenta">
        <a href="indice.php" class="link-volver">← Volver al Historial de Ventas</a>
        <h1>Registrar Nueva Venta</h1>

        <form id="formNuevaVenta">
            <label for="selectCliente">Cliente</label>
            <div class="select-cliente-wrapper" style="display: flex; gap: 1rem; align-items: center;">
                <select id="selectCliente" required style="flex: 1; margin-bottom: 0;">
                    <option value="" disabled selected>-- Seleccione un cliente --</option>
                </select>
                <button type="button" id="btnAbrirModalCliente" class="btn-secundario" style="width: auto; padding: 0.6rem 1rem; margin-bottom: 0;">+ Nuevo Cliente</button>
            </div>

            <hr class="separador-form">

            <h3>Productos en la Venta</h3>
            
            <div class="tabla-contenedor">
                <table>
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th class="th-precio">Precio Unit.</th>
                            <th class="th-cantidad">Cantidad</th>
                            <th class="th-subtotal">Subtotal</th>
                            <th class="th-accion">Acción</th>
                        </tr>
                    </thead>
                    <tbody id="tablaCarritoBody"></tbody>
                </table>
            </div>

            <div class="contenedor-agregar-producto">
                <button type="button" id="btnAddProducto" class="btn-secundario">+ Agregar Producto</button>
            </div>

            <div class="tarjeta-inventario">
                <small>Total a Cobrar</small>
                <h3 id="lblTotalVenta">$0.00</h3>
            </div>

            <button type="submit" class="btn-principal">Registrar Venta</button>
        </form>
    </main>

    <!-- MODAL NUEVO CLIENTE (Oculto por defecto) -->
    <div id="modalNuevoCliente" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.6); z-index: 1000; justify-content: center; align-items: center;">
        <div style="background: #ffffff; padding: 2rem; border-radius: 8px; width: 100%; max-width: 450px; box-shadow: 0 4px 15px rgba(0,0,0,0.2);">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; color: #1e293b;">Registrar Nuevo Cliente</h3>
            
            <form id="formNuevoClienteModal">
                <label style="display: block; margin-bottom: 1rem;">Nombre:
                    <input type="text" id="cli_nombre" required placeholder="Ej. Juan" style="width: 100%; margin-top: 0.3rem;">
                </label>

                <label style="display: block; margin-bottom: 1rem;">Apellido:
                    <input type="text" id="cli_apellido" required placeholder="Ej. Pérez" style="width: 100%; margin-top: 0.3rem;">
                </label>

                <label style="display: block; margin-bottom: 1rem;">Teléfono (Solo números):
                    <input type="tel" id="cli_telefono" required placeholder="Ej. 3764123456" pattern="[0-9]+" style="width: 100%; margin-top: 0.3rem;">
                    <small style="color: #64748b; font-size: 0.8rem;">Ingrese únicamente dígitos, sin espacios ni guiones.</small>
                </label>

                <label style="display: block; margin-bottom: 1.5rem;">Correo Electrónico:
                    <input type="email" id="cli_email" required placeholder="Ej. correo@ejemplo.com" style="width: 100%; margin-top: 0.3rem;">
                </label>

                <div style="display: flex; gap: 1rem;">
                    <button type="submit" class="btn-principal" style="flex: 1; margin-bottom: 0;">Guardar Cliente</button>
                    <button type="button" id="btnCerrarModalCliente" class="secondary" style="flex: 1; margin-bottom: 0;">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toastContainer"></div>
    <script src="../js/ventas.js"></script>
</body>
</html>