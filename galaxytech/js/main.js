function confirmarBaja(id) {
    if (confirm("¿Estás seguro de que querés dar de baja a este cliente?")) {
        window.location.href = "baja_cliente.php?id=" + id;
    }
}