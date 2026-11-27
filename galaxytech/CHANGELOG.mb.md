# Changelog

## [1.0.0] - 2026-11-26
### Agregado
- Inicio de sesión y cierre de sesión con control de sesión activa, contraseñas encriptadas (`password_hash`) y paneles diferenciados para Administrador y Vendedor (#1).
- Gestión de inventario de productos con precios, costos, stock y baja lógica (`activo = 0`) (#2).
- Gestión de clientes y usuarios internos con validaciones en cliente y servidor (campos obligatorios, valores no negativos y teléfonos solo numéricos) (#3).
- Módulo de ventas con carrito, control estricto de stock, transacciones SQL ("todo o nada") y anulación con reposición de inventario (#4).
- Seguimiento de servicio técnico con estados (*Pendiente*, *En Proceso*, *Completado* y *Entregado*) y cobro de reparaciones terminadas (#5).
- Panel principal e informes con recaudación total, cantidad de operaciones, estados de servicios y filtros por estado, cliente o fecha (#6).

### Cambiado
- Consultas y acciones de interfaz integradas de forma asíncrona mediante `Fetch API` y respuestas JSON desde PHP (#7).

### Corregido
- Prevención de ventas sin stock y protección de la integridad histórica mediante bajas lógicas en registros con movimientos asociados (#8).