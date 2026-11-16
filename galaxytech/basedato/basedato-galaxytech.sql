CREATE DATABASE galaxytech_db;

USE galaxytech_db;

CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(50) NOT NULL,
    apellido VARCHAR(50) NOT NULL,
    telefono VARCHAR(30) NOT NULL,
    email VARCHAR(100) NOT NULL,
    activo TINYINT(1) DEFAULT 1
) ENGINE=InnoDB;

-- Recordá insertar primero al Consumidor Final para garantizar id_cliente = 1:
-- INSERT INTO clientes (id_cliente, nombre, apellido, telefono, email, activo) VALUES (1, 'Consumidor', 'Final', '-', '-', 1);

CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    clave VARCHAR(255) NOT NULL,
    rol VARCHAR(50) NOT NULL,
    activo TINYINT(1) DEFAULT 1
);

CREATE TABLE productos (
    id_producto INT AUTO_INCREMENT PRIMARY KEY,
    codigo VARCHAR(50) NOT NULL UNIQUE,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    precio DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    activo TINYINT(1) DEFAULT 1
);

CREATE TABLE servicios (
    id_servicio INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL,
    id_usuario INT NOT NULL,
    equipo_descripcion VARCHAR(150) NOT NULL,
    diagnostico TEXT,
    costo DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    estado VARCHAR(50) DEFAULT 'pendiente',
    fecha_ingreso DATETIME DEFAULT CURRENT_TIMESTAMP,
    fecha_entrega DATETIME NULL DEFAULT NULL,
    activo TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario)
);

CREATE TABLE ventas (
    id_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT NOT NULL DEFAULT 1,
    id_usuario INT NOT NULL,
    id_servicio INT NULL DEFAULT NULL,
    total DECIMAL(10,2) NOT NULL,
    fecha_venta DATETIME DEFAULT CURRENT_TIMESTAMP,
    estado TINYINT(1) DEFAULT 1,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id_cliente),
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_servicio) REFERENCES servicios(id_servicio)
);

CREATE TABLE detalle_ventas (
    id_detalle_venta INT AUTO_INCREMENT PRIMARY KEY,
    id_venta INT NOT NULL,
    id_producto INT NOT NULL,
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (id_venta) REFERENCES ventas(id_venta),
    FOREIGN KEY (id_producto) REFERENCES productos(id_producto)
);