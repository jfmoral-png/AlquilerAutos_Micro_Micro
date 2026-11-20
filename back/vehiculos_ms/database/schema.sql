CREATE DATABASE IF NOT EXISTS vehiculos_db
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE vehiculos_db;

DROP TABLE IF EXISTS vehiculos;

CREATE TABLE vehiculos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    marca VARCHAR(100) NOT NULL,
    modelo VARCHAR(100) NOT NULL,
    anio YEAR NOT NULL,
    categoria VARCHAR(50),
    estado ENUM('disponible', 'alquilado', 'mantenimiento') DEFAULT 'disponible',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

INSERT INTO vehiculos (id, marca, modelo, anio, categoria, estado) VALUES
(1, 'Toyota',  'Corolla',  2023, 'Sedán',     'disponible'),
(2, 'Hyundai', 'Tucson',   2027, 'SUV',       'disponible'),
(3, 'Kia',     'Picanto',  2021, 'Hatchback', 'mantenimiento'),
(4, 'Nissan',  'Frontier', 2024, 'Pickup',    'alquilado'),
(5, 'Mazda',   'CX-5',     2022, 'SUV',       'disponible'),
(6, 'Renault', 'Logan',    2020, 'Sedán',     'disponible');
