-- Crear tabla usuarios si no existe
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    username VARCHAR(50) UNIQUE,
    rol ENUM('admin', 'usuario') DEFAULT 'usuario' NOT NULL,
    fecha_creacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    ultima_actualizacion TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Asegurarse de que existe al menos un usuario administrador
INSERT IGNORE INTO usuarios (nombre, email, username, rol) 
VALUES ('Administrador', 'admin@clinica.com', 'admin', 'admin');
