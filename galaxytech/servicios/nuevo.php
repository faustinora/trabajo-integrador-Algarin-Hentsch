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
    <title>Nuevo Servicio - GalaxyTech</title>
    <link rel="stylesheet" href="../css/estilos.css">
</head>
<body>
    <main class="container contenedor-principal contenedor-mediano">
        <a href="indice.php" class="link-volver">← Volver al Listado</a>
        <h1>Registrar Nuevo Servicio Técnico</h1>

        <form id="formNuevoServicio">
            <label for="selectCliente">Cliente</label>
            <div class="select-cliente-wrapper">
                <select id="selectCliente" required>
                    <option value="" disabled selected>Cargando clientes...</option>
                </select>
                <button type="button" id="btnNuevoClienteModal" class="btn-secundario-inline">+ Nuevo</button>
            </div>

            <label for="equipo_descripcion" class="label-espaciado">Descripción del Equipo / Modelo
                <input type="text" id="equipo_descripcion" required placeholder="Ej. Notebook HP Pavilion 15 - No enciende">
            </label>

            <label for="diagnostico">Diagnóstico / Falla Reportada
                <textarea id="diagnostico" placeholder="Detalles de la revisión o problema manifestado..."></textarea>
            </label>

            <label for="costo">Costo Estimado ($)
                <input type="number" step="0.01" id="costo" required value="0.00">
            </label>

            <label for="estado">Estado Inicial
                <select id="estado">
                    <option value="pendiente" selected>Pendiente</option>
                    <option value="en proceso">En Proceso</option>
                    <option value="completado">Completado</option>
                </select>
            </label>

            <button type="submit" class="btn-principal btn-espaciado">Guardar Servicio</button>
        </form>
    </main>

    <div id="modalCliente" class="modal-overlay">
        <div class="modal-content contenedor-mediano">
            <h3>Registrar Nuevo Cliente</h3>
            <form id="formModalCliente">
                <label for="modal_apellido">Apellido
                    <input type="text" id="modal_apellido" required>
                </label>
                <label for="modal_nombre">Nombre
                    <input type="text" id="modal_nombre" required>
                </label>
                <label for="modal_telefono">Teléfono
                    <input type="text" id="modal_telefono">
                </label>
                <label for="modal_email">Email (opcional)
                    <input type="email" id="modal_email">
                </label>
                <div class="modal-acciones">
                    <button type="submit" class="btn-principal">Guardar</button>
                    <button type="button" id="btnCerrarModal" class="secondary">Cancelar</button>
                </div>
            </form>
        </div>
    </div>

    <div id="toastContainer"></div>
    <script src="../js/servicios.js"></script>
</body>
</html>