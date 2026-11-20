CREATE DATABASE IF NOT EXISTS clientes_db
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE clientes_db;

DROP TABLE IF EXISTS clientes;

CREATE TABLE clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(150) NOT NULL,
    telefono VARCHAR(50),
    correo VARCHAR(100),
    numero_licencia VARCHAR(50),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO clientes (id, nombre, telefono, correo, numero_licencia) VALUES
(1, 'Ana Rodríguez',   '8888-1111', 'ana@correo.com',    'LIC-1001'),
(2, 'Carlos Méndez',   '8777-2222', 'carlos@correo.com', 'LIC-1002'),
(3, 'María Fernández', '8666-3333', 'maria@correo.com',  'LIC-1003'),
(4, 'Luis Gómez',      '8555-4444', 'luis@correo.com',   'LIC-1004'),
(5, 'Laura Torres',    '8444-5555', 'laura@correo.com',  'LIC-1005');
