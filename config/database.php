<?php
define('DB_SERVER', 'localhost');
define('DB_USERNAME', 'root');
define('DB_PASSWORD', '');
define('DB_NAME', 'clinica_fertilidad');

// Intentar conectar a MySQL
$conn = mysqli_connect(DB_SERVER, DB_USERNAME, DB_PASSWORD);

if (!$conn) {
    die("Error en la conexión a MySQL: " . mysqli_connect_error());
}

// Crear la base de datos si no existe
$sql = "CREATE DATABASE IF NOT EXISTS " . DB_NAME;
if (!mysqli_query($conn, $sql)) {
    die("Error al crear la base de datos: " . mysqli_error($conn));
}

// Seleccionar la base de datos
if (!mysqli_select_db($conn, DB_NAME)) {
    die("Error al seleccionar la base de datos: " . mysqli_error($conn));
}

// Crear tabla de usuarios si no existe
$sql = "CREATE TABLE IF NOT EXISTS usuarios (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    rol ENUM('admin', 'doctor', 'staff') NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $sql)) {
    die("Error al crear la tabla usuarios: " . mysqli_error($conn));
}

// Verificar si la tabla existe y está vacía
$check_table = mysqli_query($conn, "SELECT COUNT(*) as count FROM usuarios");
if ($check_table) {
    $row = mysqli_fetch_assoc($check_table);
    if ($row['count'] == 0) {
        // La tabla está vacía, crear usuario administrador por defecto
        $nombre = "Admin";
        $email = "admin@clinica.com";
        $password = password_hash("admin123", PASSWORD_DEFAULT);
        $rol = "admin";

        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
        if ($stmt = mysqli_prepare($conn, $sql)) {
            mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $password, $rol);
            if (!mysqli_stmt_execute($stmt)) {
                die("Error al crear usuario administrador: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt);
        }
    }
}

// Insertar médicos por defecto si no existen
$medicos = [
    ['Dr. Juan Pérez', 'doctor1@example.com', 'doctor'],
    ['Dra. María García', 'doctor2@example.com', 'doctor'],
    ['Dr. Carlos Rodríguez', 'doctor3@example.com', 'doctor'],
    ['Dra. Ana López', 'doctor4@example.com', 'doctor']
];

foreach ($medicos as $medico) {
    $nombre = $medico[0];
    $email = $medico[1];
    $rol = $medico[2];
    
    // Verificar si el médico ya existe
    $check = mysqli_query($conn, "SELECT id FROM usuarios WHERE email = '$email'");
    if (mysqli_num_rows($check) == 0) {
        $password = password_hash('123456', PASSWORD_DEFAULT); // Contraseña por defecto: 123456
        mysqli_query($conn, "INSERT INTO usuarios (nombre, email, password, rol) VALUES ('$nombre', '$email', '$password', '$rol')");
    }
}

// Crear tabla de sucursales si no existe
$sql = "CREATE TABLE IF NOT EXISTS sucursales (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $sql)) {
    die("Error al crear la tabla sucursales: " . mysqli_error($conn));
}

// Insertar sucursales por defecto si la tabla está vacía
$check_sucursales = mysqli_query($conn, "SELECT COUNT(*) as count FROM sucursales");
if ($check_sucursales) {
    $row = mysqli_fetch_assoc($check_sucursales);
    if ($row['count'] == 0) {
        $sucursales = [
            'Mexicali',
            'Mexico DF',
            'Aguascalientes'
        ];
        
        foreach ($sucursales as $sucursal) {
            $sql = "INSERT INTO sucursales (nombre) VALUES (?)";
            if ($stmt = mysqli_prepare($conn, $sql)) {
                mysqli_stmt_bind_param($stmt, "s", $sucursal);
                if (!mysqli_stmt_execute($stmt)) {
                    die("Error al crear sucursal: " . mysqli_error($conn));
                }
                mysqli_stmt_close($stmt);
            }
        }
    }
}

// Crear tabla de grupos si no existe
$sql = "CREATE TABLE IF NOT EXISTS grupos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    sucursal_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (sucursal_id) REFERENCES sucursales(id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $sql)) {
    die("Error al crear la tabla grupos: " . mysqli_error($conn));
}

// Crear tabla de horarios_grupo si no existe
$sql = "CREATE TABLE IF NOT EXISTS horarios_grupo (
    id INT PRIMARY KEY AUTO_INCREMENT,
    grupo_id INT NOT NULL,
    dia_semana INT NOT NULL COMMENT '0=Domingo, 1=Lunes, ..., 6=Sábado',
    hora_inicio TIME NOT NULL,
    hora_fin TIME NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if (!mysqli_query($conn, $sql)) {
    die("Error al crear la tabla horarios_grupo: " . mysqli_error($conn));
}

// Crear tabla de medicos_grupo si no existe
$sql = "CREATE TABLE IF NOT EXISTS medicos_grupo (
    id INT AUTO_INCREMENT PRIMARY KEY,
    grupo_id INT NOT NULL,
    usuario_id INT NOT NULL,
    tipo_lab VARCHAR(2) DEFAULT NULL,
    FOREIGN KEY (grupo_id) REFERENCES grupos(id) ON DELETE CASCADE,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    UNIQUE KEY unique_medico_grupo (grupo_id, usuario_id)
)";
    
if (!mysqli_query($conn, $sql)) {
    die("Error al crear la tabla medicos_grupo: " . mysqli_error($conn));
}

// Agregar columna tipo_lab si no existe
$result = mysqli_query($conn, "SHOW COLUMNS FROM medicos_grupo LIKE 'tipo_lab'");
if (mysqli_num_rows($result) == 0) {
    $sql = "ALTER TABLE medicos_grupo ADD COLUMN tipo_lab VARCHAR(2) DEFAULT NULL";
    if (!mysqli_query($conn, $sql)) {
        die("Error al agregar columna tipo_lab: " . mysqli_error($conn));
    }
}

?>
