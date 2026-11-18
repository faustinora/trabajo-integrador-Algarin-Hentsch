# Proyecto Integrador - GalaxyTech (Algarín & Hentsch)

Sistema de gestión web desarrollado para administrar la actividad diaria de **GalaxyTech**, un local dedicado a la venta de productos informáticos y servicio técnico de equipos. 

El proyecto integra los contenidos trabajados en la materia *Lenguajes Generadores de Informes*: **HTML, CSS, PHP, MySQL, JavaScript, Fetch y Git**, partiendo desde el diseño de la base de datos hasta la interfaz de usuario sin utilizar frameworks externos.

---

## ¿De qué trata el sistema?

El sistema resuelve la administración de un comercio tecnológico organizando tres ejes principales:
1. **Lo que se gestiona:** Productos del inventario (con control de precios y stock) y equipos ingresados a reparación.
2. **Quiénes operan y usan el sistema:** Los clientes del local (quienes compran o dejan equipos) y los usuarios internos del negocio (Administradores y Vendedores).
3. **Las operaciones principales:** 
   - **Ventas:** Registro de compras vinculando productos con clientes, calculando totales y descontando el stock automáticamente.
   - **Servicio Técnico:** Seguimiento de reparaciones mediante estados (*Pendiente*, *En Proceso*, *Completado* y *Entregado*), permitiendo luego cobrar el servicio terminado directamente en caja.

---

## Herramientas Utilizadas

* **Estructura y Diseño (HTML5 y CSS3):** Maquetación de las pantallas principales, formularios, tablas de listado y ventanas modales con un estilo unificado y claro para el usuario.
* **Dinamismo en el Navegador (JavaScript y Fetch API):** Permite que las búsquedas, filtros, altas, ediciones y bajas funcionen de manera fluida sin necesidad de recargar toda la página en cada acción.
* **Lógica del Servidor (PHP):** Procesamiento de las peticiones en formato JSON, control de sesiones activas y aplicación de las reglas de negocio antes de guardar cualquier dato.
* **Base de Datos (MySQL):** Almacenamiento estructurado de la información mediante tablas relacionadas con claves foráneas para evitar datos sueltos o inconsistentes.
* **Trabajo Colaborativo (Git y GitHub):** Organización del código mediante ramas por funcionalidad y revisión de cambios entre ambos integrantes antes de unificar en la rama principal.

---

## Validaciones y Reglas de Control Importantes

Para garantizar que los datos sean seguros y evitar errores operativos en el negocio, el sistema aplica controles tanto en el navegador (JavaScript) como en el servidor (PHP):

* **Protección de Acceso y Contraseñas:** Ninguna pantalla ni archivo de datos puede abrirse si no hay una sesión iniciada. Además, las claves de los usuarios se guardan encriptadas (`password_hash`) y nunca en texto plano.
* **Control estricto de Stock en Ventas:** Al cargar productos en el carrito, el sistema impide seleccionar cantidades mayores al stock disponible. Antes de confirmar la venta en el servidor, se vuelve a verificar la disponibilidad real para evitar ventas sin mercadería.
* **Operaciones "Todo o Nada" (Transacciones):** Al registrar una venta o anularla, si ocurre algún fallo a mitad del proceso, el sistema deshace los cambios automáticamente. Si una venta se anula, el stock de los productos vuelve a sumarse al inventario.
* **Bajas Lógicas (Integridad de Datos):** Los productos, clientes o servicios que ya tienen movimientos asociados no se borran definitivamente de la base de datos, sino que se desactivan (`activo = 0`) para no perder el historial ni romper los informes.
* **Validación de Datos de Entrada:** Se controla que los campos obligatorios no queden vacíos, que los costos y precios no sean negativos, y que datos como el teléfono de un cliente contengan únicamente números.

---

## Panel e Informes

El sistema incluye una pantalla principal con indicadores clave del negocio (recaudación total, cantidad de operaciones registradas y estados de los servicios) junto con listados filtrables por estado, cliente o fecha para facilitar el control diario.

---

## Instalación y Puesta en Marcha

1. **Descargar el proyecto:** Clonar o copiar la carpeta del repositorio dentro del directorio público del servidor local (por ejemplo, `htdocs` en XAMPP).
2. **Crear la base de datos:** 
   - Abrir *phpMyAdmin* (o el gestor de MySQL preferido) y crear una base de datos llamada `galaxytech_db`.
   - Importar el archivo `.sql` ubicado en la carpeta `database/` que contiene las tablas y los datos iniciales de prueba.
3. **Configurar la conexión:** Revisar el archivo `config/conexion.php` y verificar que el usuario, contraseña y nombre de la base de datos coincidan con los de tu servidor local.
4. **Ingresar al sistema:** Iniciar Apache y MySQL, abrir el navegador e ingresar a `http://localhost/trabajo-integrador-Algarin-Hentsch/galaxytech/login.php`.

### Usuarios de Prueba
* **Administrador:** Usuario `admin` | Contraseña `admin123` *(acceso total a productos, usuarios, ventas, servicios e informes)*.
* **Vendedor:** Usuario `vendedor1` | Contraseña `vendedor123` *(acceso a ventas, clientes y seguimiento de servicios técnicos)*.

---

## Organización del Equipo (Quién hizo qué)

El desarrollo se llevó a cabo en pareja, reutilizando componentes base (como la conexión y los estilos generales) y dividiendo las responsabilidades principales para luego integrarlas:

* **Hentsch, Alan:** Encargado del desarrollo de las pantallas base y la lógica central de negocio: sistema de inicio de sesión, paneles de Administrador y Vendedor, conexión a la base de datos, resumen general, gestión de productos y registro de ventas.
* **Algarín, Ramón:** Encargado del diseño visual y hojas de estilo (`estilos.css`), programación de las funciones en JavaScript y consumo de datos con `fetch`, gestión de usuarios, clientes y desarrollo de los endpoints de la API.
