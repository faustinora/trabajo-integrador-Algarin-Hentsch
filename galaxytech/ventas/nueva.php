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
        <h1>Registrar Nueva Venta / Cobro</h1>

        <form id="formNuevaVenta">
            <label for="selectCliente">Cliente</label>
            <div class="select-cliente-flex">
                <select id="selectCliente" required>
                    <option value="" disabled selected>-- Seleccione un cliente --</option>
                </select>
                <button type="button" id="btnAbrirModalCliente" class="btn-secundario btn-cliente-inline">+ Nuevo Cliente</button>
            </div>

            <hr class="separador-form">

            <div id="infoServicioCobro">
                <h3 class="titulo-servicio-cobro">Concepto: Servicio Técnico</h3>
                <p id="txtDetalleEquipo" class="detalle-servicio-cobro"></p>
                <p class="monto-servicio-cobro">Monto de la Reparación: <span id="lblCostoServicio">$0.00</span></p>
            </div>

            <h3>Productos o Repuestos Opcionales</h3>

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
                <button type="button" id="btnAddProducto" class="btn-secundario">+ Agregar Repuesto / Producto</button>
            </div>

            <div class="tarjeta-inventario">
                <small>Total a Cobrar</small>
                <h3 id="lblTotalVenta">$0.00</h3>
            </div>

            <button type="submit" class="btn-principal">Registrar Venta / Cobro</button>
        </form>
    </main>

    <div id="modalNuevoCliente" class="modal-overlay">
        <div class="modal-tarjeta-clara">
            <h3>Registrar Nuevo Cliente</h3>

            <form id="formNuevoClienteModal">
                <label>Nombre:
                    <input type="text" id="cli_nombre" required placeholder="Ej. Juan">
                </label>

                <label>Apellido:
                    <input type="text" id="cli_apellido" required placeholder="Ej. Pérez">
                </label>

                <label>Teléfono (Solo números):
                    <input type="tel" id="cli_telefono" required placeholder="Ej. 3764123456" pattern="[0-9]+">
                    <small>Ingrese únicamente dígitos, sin espacios ni guiones.</small>
                </label>

                <label>Correo Electrónico:
                    <input type="email" id="cli_email" required placeholder="Ej. correo@ejemplo.com">
                </label>

                <div class="modal-acciones">
                    <button type="submit" class="btn-principal">Guardar Cliente</button>
                    <button type="button" id="btnCerrarModalCliente" class="secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toastContainer"></div>
    <script src="../js/ventas.js"></script>
</body>
</html>