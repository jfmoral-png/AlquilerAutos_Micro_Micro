CREATE DATABASE IF NOT EXISTS reservas_db
    DEFAULT CHARACTER SET utf8mb4
    COLLATE utf8mb4_unicode_ci;

USE reservas_db;

DROP TABLE IF EXISTS reservas;

CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    vehiculo_id INT NOT NULL,
    fecha_inicio DATE NOT NULL,
    fecha_fin DATE NOT NULL,
    estado ENUM('activa', 'completada', 'cancelada') DEFAULT 'activa',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_reservas_cliente (cliente_id),
    INDEX idx_reservas_vehiculo (vehiculo_id)
) ENGINE=InnoDB;

INSERT INTO reservas (id, cliente_id, vehiculo_id, fecha_inicio, fecha_fin, estado) VALUES
(1, 1, 4, '2026-11-16', '2026-11-20', 'activa'),
(2, 2, 1, '2026-11-20', '2026-11-23', 'activa'),
(3, 3, 2, '2026-11-05', '2026-11-09', 'completada'),
(4, 4, 5, '2026-12-01', '2026-12-05', 'activa'),
(5, 5, 6, '2026-10-10', '2026-10-14', 'completada');
