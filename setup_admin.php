<?php
require_once "config/database.php";

// Limpiar la tabla de usuarios
$sql = "TRUNCATE TABLE usuarios";
if(mysqli_query($conn, $sql)){
    echo "Tabla usuarios limpiada correctamente.<br>";
} else {
    echo "Error al limpiar la tabla: " . mysqli_error($conn) . "<br>";
}

// Crear el usuario administrador
$nombre = "Admin";
$email = "admin@clinica.com";
$password = password_hash("admin123", PASSWORD_DEFAULT);
$rol = "admin";

// Mostrar datos para debug
echo "Debug: Password hash generado: " . $password . "<br>";

$sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";

if($stmt = mysqli_prepare($conn, $sql)){
    mysqli_stmt_bind_param($stmt, "ssss", $nombre, $email, $password, $rol);
    
    if(mysqli_stmt_execute($stmt)){
        echo "Usuario administrador creado exitosamente.<br>";
        echo "Email: admin@clinica.com<br>";
        echo "Contraseña: admin123<br>";
        
        // Verificar que el usuario se creó correctamente
        $check_sql = "SELECT * FROM usuarios WHERE email = 'admin@clinica.com'";
        $result = mysqli_query($conn, $check_sql);
        if($row = mysqli_fetch_assoc($result)){
            echo "<br>Verificación de usuario creado:<br>";
            echo "ID: " . $row['id'] . "<br>";
            echo "Nombre: " . $row['nombre'] . "<br>";
            echo "Email: " . $row['email'] . "<br>";
            echo "Rol: " . $row['rol'] . "<br>";
        }
        
        echo "<br><a href='index.php'>Ir al login</a>";
    } else{
        echo "Error al crear el usuario administrador: " . mysqli_error($conn);
    }
    mysqli_stmt_close($stmt);
} else {
    echo "Error en la preparación de la consulta: " . mysqli_error($conn);
}

mysqli_close($conn);
?>
