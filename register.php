<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["rol"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

$nombre = $email = $password = $confirm_password = $rol = "";
$nombre_err = $email_err = $password_err = $confirm_password_err = $rol_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["nombre"]))){
        $nombre_err = "Por favor ingrese el nombre.";
    } else{
        $nombre = trim($_POST["nombre"]);
    }

    if(empty(trim($_POST["email"]))){
        $email_err = "Por favor ingrese un email.";
    } else{
        $sql = "SELECT id FROM usuarios WHERE email = ?";
        
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "s", $param_email);
            $param_email = trim($_POST["email"]);
            
            if(mysqli_stmt_execute($stmt)){
                mysqli_stmt_store_result($stmt);
                
                if(mysqli_stmt_num_rows($stmt) == 1){
                    $email_err = "Este email ya está registrado.";
                } else{
                    $email = trim($_POST["email"]);
                }
            } else{
                echo "Error en el sistema. Por favor intente más tarde.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Por favor ingrese una contraseña.";     
    } elseif(strlen(trim($_POST["password"])) < 6){
        $password_err = "La contraseña debe tener al menos 6 caracteres.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty(trim($_POST["confirm_password"]))){
        $confirm_password_err = "Por favor confirme la contraseña.";     
    } else{
        $confirm_password = trim($_POST["confirm_password"]);
        if(empty($password_err) && ($password != $confirm_password)){
            $confirm_password_err = "Las contraseñas no coinciden.";
        }
    }

    if(empty(trim($_POST["rol"]))){
        $rol_err = "Por favor seleccione un rol.";
    } else{
        $rol = trim($_POST["rol"]);
    }
    
    if(empty($nombre_err) && empty($email_err) && empty($password_err) && empty($confirm_password_err) && empty($rol_err)){
        $sql = "INSERT INTO usuarios (nombre, email, password, rol) VALUES (?, ?, ?, ?)";
         
        if($stmt = mysqli_prepare($conn, $sql)){
            mysqli_stmt_bind_param($stmt, "ssss", $param_nombre, $param_email, $param_password, $param_rol);
            
            $param_nombre = $nombre;
            $param_email = $email;
            $param_password = password_hash($password, PASSWORD_DEFAULT);
            $param_rol = $rol;
            
            if(mysqli_stmt_execute($stmt)){
                header("location: users.php");
            } else{
                echo "Error en el sistema. Por favor intente más tarde.";
            }

            mysqli_stmt_close($stmt);
        }
    }
    
    mysqli_close($conn);
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario - Clínica de Fertilidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-color: #4e73df;
            --secondary-color: #2e59d9;
            --success-color: #1cc88a;
            --info-color: #36b9cc;
            --card-border-radius: 16px;
            --input-border-radius: 8px;
        }
        
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .card {
            border-radius: var(--card-border-radius);
            border: none;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .card-header {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            color: #fff;
            padding: 1.5rem;
            border-bottom: none;
        }
        
        .card-header h2 {
            font-weight: 600;
            margin-bottom: 0;
        }
        
        .card-body {
            padding: 2rem;
        }
        
        .form-control {
            border-radius: var(--input-border-radius);
            padding: 0.75rem 1rem;
            border: 1px solid #e2e8f0;
            background-color: #f8fafc;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(78, 115, 223, 0.15);
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);
            border: none;
            border-radius: var(--input-border-radius);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, var(--secondary-color) 0%, var(--primary-color) 100%);
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.3);
        }
        
        .btn-secondary {
            background-color: #fff;
            color: var(--primary-color);
            border: 1px solid #e2e8f0;
            border-radius: var(--input-border-radius);
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-secondary:hover {
            background-color: #f8fafc;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
        }
        
        .input-group-text {
            background-color: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: var(--input-border-radius);
        }
        
        a {
            color: var(--primary-color);
            text-decoration: none;
            transition: color 0.2s ease;
        }
        
        a:hover {
            color: var(--secondary-color);
        }
        
        /* Animación para el formulario */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translate3d(0, 30px, 0);
            }
            to {
                opacity: 1;
                transform: translate3d(0, 0, 0);
            }
        }
        
        .animate-form {
            animation: fadeInUp 0.5s ease;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-md-8 col-lg-6">
                <div class="text-center mb-4">
                    <h1 class="text-dark mb-3">Clínica de Fertilidad</h1>
                    <p class="text-muted">Panel de Administración</p>
                </div>
                
                <div class="card animate-form">
                    <div class="card-header">
                        <h2 class="text-center">
                            <i class="bi bi-person-plus me-2"></i>Registro de Usuario
                        </h2>
                    </div>
                    <div class="card-body">
                        <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                            <div class="mb-4">
                                <label for="nombre" class="form-label">Nombre completo</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-person"></i></span>
                                    <input type="text" id="nombre" name="nombre" class="form-control <?php echo (!empty($nombre_err)) ? 'is-invalid' : ''; ?>" placeholder="Ingrese el nombre completo" value="<?php echo $nombre; ?>">
                                    <?php if(!empty($nombre_err)): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $nombre_err; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="email" class="form-label">Correo electrónico</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                    <input type="email" id="email" name="email" class="form-control <?php echo (!empty($email_err)) ? 'is-invalid' : ''; ?>" placeholder="Ingrese el correo electrónico" value="<?php echo $email; ?>">
                                    <?php if(!empty($email_err)): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $email_err; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row">
                                <div class="col-md-6 mb-4">
                                    <label for="password" class="form-label">Contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key"></i></span>
                                        <input type="password" id="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Cree una contraseña" value="<?php echo $password; ?>">
                                        <?php if(!empty($password_err)): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $password_err; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="col-md-6 mb-4">
                                    <label for="confirm_password" class="form-label">Confirmar contraseña</label>
                                    <div class="input-group">
                                        <span class="input-group-text"><i class="bi bi-key-fill"></i></span>
                                        <input type="password" id="confirm_password" name="confirm_password" class="form-control <?php echo (!empty($confirm_password_err)) ? 'is-invalid' : ''; ?>" placeholder="Repita la contraseña" value="<?php echo $confirm_password; ?>">
                                        <?php if(!empty($confirm_password_err)): ?>
                                        <div class="invalid-feedback">
                                            <?php echo $confirm_password_err; ?>
                                        </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label for="rol" class="form-label">Rol del usuario</label>
                                <div class="input-group">
                                    <span class="input-group-text"><i class="bi bi-shield-lock"></i></span>
                                    <select id="rol" name="rol" class="form-select <?php echo (!empty($rol_err)) ? 'is-invalid' : ''; ?>">
                                        <option value="" disabled selected>Seleccione un rol</option>
                                        <option value="admin" <?php echo ($rol == "admin") ? 'selected' : ''; ?>>Administrador</option>
                                        <option value="doctor" <?php echo ($rol == "doctor") ? 'selected' : ''; ?>>Doctor</option>
                                        <option value="staff" <?php echo ($rol == "staff") ? 'selected' : ''; ?>>Staff</option>
                                    </select>
                                    <?php if(!empty($rol_err)): ?>
                                    <div class="invalid-feedback">
                                        <?php echo $rol_err; ?>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="row mb-3 mt-4 pt-2">
                                <div class="col-md-6 mb-2">
                                    <button type="submit" class="btn btn-primary w-100">
                                        <i class="bi bi-check-circle me-2"></i>Registrar Usuario
                                    </button>
                                </div>
                                <div class="col-md-6 mb-2">
                                    <a href="users.php" class="btn btn-secondary w-100">
                                        <i class="bi bi-arrow-left me-2"></i>Volver a Usuarios
                                    </a>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <div class="text-center mt-4">
                    <p class="small text-muted">
                        &copy; <?php echo date("Y"); ?> Clínica de Fertilidad. Todos los derechos reservados.
                    </p>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
