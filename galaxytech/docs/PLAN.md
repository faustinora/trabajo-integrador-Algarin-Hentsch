# Plan de trabajo

## Sprint 1 (21/11 al 23/11) → v1.0.0
Objetivo: consolidar el alcance principal del sistema integrando autenticación, ABM, ventas con control de stock, servicio técnico e informes sin errores críticos.

| Issue | Tarea | Responsable | Estado |
| :--- | :--- | :--- | :--- |
| #1 | Login, sesiones, hashes de contraseña y paneles Admin/Vendedor | @alanhentsch | Hecho |
| #2 | Gestión de productos y baja lógica de inventario | @alanhentsch | Hecho |
| #3 | Gestión de clientes y usuarios con validaciones de entrada | @ramonalgarin | Hecho |
| #4 | Registro y anulación de ventas con transacciones SQL y control de stock | @alanhentsch | Hecho |
| #5 | Seguimiento de estados y cobro de servicio técnico | @ramonalgarin | Hecho |
| #6 | Panel de indicadores e informes filtrables | @alanhentsch | Hecho |

Retro:
- Bien: división clara de tareas entre lógica central/paneles y vistas/endpoints/JS, con reglas de integridad bien definidas.
- Mal: hubo que alinear rutas y detalles de documentación entre el código y el README.
- Cambiamos: revisamos y unificamos las referencias de instalación, roles y rutas antes de cerrar la entrega.

## Sprint 2 (24/11 al 25/11) → v1.1.0
Objetivo: pulir validaciones, filtros de búsqueda y coherencia de documentación tras la revisión del MVP.

| Issue | Tarea | Responsable | Estado |
| :--- | :--- | :--- | :--- |
| #7 | Revisión de respuestas JSON en endpoints de API y manejo de errores con Fetch | @ramonalgarin | En curso |
| #8 | Pruebas integrales de rollback en ventas anuladas y permisos por rol | @alanhentsch | En curso |

## Fuera de alcance (futura v2.0.0)
- Exportación de reportes a PDF — no entra en la iteración actual por prioridad en las reglas de negocio y estabilidad del flujo principal.