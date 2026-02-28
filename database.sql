-- Sistema de Inventario General (SIG)
-- PHP 8.2 + MySQL 8.0

CREATE DATABASE IF NOT EXISTS sig_inventario CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE sig_inventario;

-- Tabla de Usuarios con seguridad reforzada
CREATE TABLE usuarios (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    nombre_completo VARCHAR(100) NOT NULL,
    rol ENUM('admin', 'supervisor', 'operador') DEFAULT 'operador',
    activo BOOLEAN DEFAULT TRUE,
    intentos_fallidos TINYINT UNSIGNED DEFAULT 0,
    bloqueado_hasta TIMESTAMP NULL,
    ultimo_acceso TIMESTAMP NULL,
    session_token VARCHAR(64) NULL,
    ip_ultimo_acceso VARCHAR(45) NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_username (username),
    INDEX idx_session (session_token)
) ENGINE=InnoDB;

-- Tabla de Productos (soporta múltiples códigos de barras por producto)
CREATE TABLE productos (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    codigo_producto VARCHAR(50) NOT NULL,
    codigo_barra VARCHAR(50) NOT NULL,
    nombre VARCHAR(200) NOT NULL,
    descripcion TEXT,
    categoria_id INT UNSIGNED,
    unidad_medida VARCHAR(20) DEFAULT 'UNIDAD',
    stock_teorico DECIMAL(10,2) DEFAULT 0,
    stock_fisico DECIMAL(10,2) DEFAULT 0,
    diferencia DECIMAL(10,2) GENERATED ALWAYS AS (stock_fisico - stock_teorico) STORED,
    precio_costo DECIMAL(10,2) DEFAULT 0,
    precio_venta DECIMAL(10,2) DEFAULT 0,
    ubicacion VARCHAR(100),
    activo BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uk_codigo_barra (codigo_barra),
    INDEX idx_codigo_producto (codigo_producto),
    INDEX idx_nombre (nombre),
    INDEX idx_categoria (categoria_id)
) ENGINE=InnoDB;

-- Tabla de Sesiones de Inventario (conteos independientes)
CREATE TABLE sesiones_inventario (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    fecha_inicio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    fecha_cierre TIMESTAMP NULL,
    estado ENUM('activa', 'pausada', 'cerrada') DEFAULT 'activa',
    created_by INT UNSIGNED NOT NULL,
    FOREIGN KEY (created_by) REFERENCES usuarios(id),
    INDEX idx_estado (estado)
) ENGINE=InnoDB;

-- Tabla de Conteos en Tiempo Real (SIN DUPLICADOS, SUMATORIA AUTOMÁTICA)
CREATE TABLE conteos_tiempo_real (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    sesion_id INT UNSIGNED NOT NULL,
    producto_id INT UNSIGNED NOT NULL,
    codigo_escaneado VARCHAR(50) NOT NULL, -- Código que se escaneó (puede ser producto o barra)
    cantidad DECIMAL(10,2) NOT NULL DEFAULT 1,
    contador_id INT UNSIGNED NOT NULL,     -- Usuario que contó
    dispositivo_info VARCHAR(200),         -- Info del dispositivo/scanner
    hash_unico VARCHAR(64) NOT NULL,       -- Prevenir duplicados exactos
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sesion_id) REFERENCES sesiones_inventario(id) ON DELETE CASCADE,
    FOREIGN KEY (producto_id) REFERENCES productos(id),
    FOREIGN KEY (contador_id) REFERENCES usuarios(id),
    UNIQUE KEY uk_hash (hash_unico),       -- Evita duplicados exactos
    INDEX idx_sesion_producto (sesion_id, producto_id),
    INDEX idx_tiempo (created_at)
) ENGINE=InnoDB;

-- Vista para totales en tiempo real (MUY IMPORTANTE)
CREATE VIEW vista_conteos_agrupados AS
SELECT 
    sesion_id,
    producto_id,
    p.codigo_producto,
    p.codigo_barra,
    p.nombre,
    SUM(c.cantidad) as total_contado,
    COUNT(DISTINCT c.contador_id) as contadores_distintos,
    MAX(c.created_at) as ultimo_conteo
FROM conteos_tiempo_real c
JOIN productos p ON c.producto_id = p.id
GROUP BY sesion_id, producto_id, p.codigo_producto, p.codigo_barra, p.nombre;

-- Tabla de Logs de Seguridad
CREATE TABLE logs_seguridad (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT UNSIGNED,
    accion VARCHAR(50) NOT NULL,
    detalle TEXT,
    ip_address VARCHAR(45) NOT NULL,
    user_agent TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_usuario (usuario_id),
    INDEX idx_fecha (created_at)
) ENGINE=InnoDB;

-- Insertar usuario admin por defecto (password: Admin123!)
INSERT INTO usuarios (username, email, password_hash, nombre_completo, rol) VALUES 
('admin', 'admin@sig.com', '$2y$12$8FqKpL8xO8Z9QqQqQqQqQqO8Z9QqQqQqQqQqQqQqQqQqQqQqQqQqQqQq', 'Administrador SIG', 'admin');