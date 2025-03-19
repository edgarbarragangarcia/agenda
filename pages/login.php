<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: dashboard.html");
    exit;
}

require_once "../config/database.php";

$username = $password = "";
$username_err = $password_err = $login_err = "";

if($_SERVER["REQUEST_METHOD"] == "POST"){
    if(empty(trim($_POST["username"]))){
        $username_err = "Por favor ingrese su nombre de usuario.";
    } else{
        $username = trim($_POST["username"]);
    }
    
    if(empty(trim($_POST["password"]))){
        $password_err = "Por favor ingrese su contraseña.";
    } else{
        $password = trim($_POST["password"]);
    }
    
    if(empty($username_err) && empty($password_err)){
        // Usuarios de prueba
        $test_users = [
            'admin' => [
                'password' => '12345',
                'name' => 'Administrador',
                'email' => 'admin@clinica.com',
                'rol' => 'admin'
            ],
            'user' => [
                'password' => '12345',
                'name' => 'Usuario Normal',
                'email' => 'usuario@clinica.com',
                'rol' => 'usuario'
            ]
        ];
        
        if (isset($test_users[$username]) && $test_users[$username]['password'] === $password) {
            $_SESSION["loggedin"] = true;
            $_SESSION["username"] = $username;
            $_SESSION["name"] = $test_users[$username]['name'];
            $_SESSION["email"] = $test_users[$username]['email'];
            $_SESSION["rol"] = $test_users[$username]['rol'];
            
            header("location: dashboard.html");
        } else {
            $login_err = "Usuario o contraseña incorrectos.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Agenda INGENES</title>
    <?php include '../includes/modern-styles.php'; ?>
    <style>
        :root {
            --primary-color: #2C3E50;
            --secondary-color: #3498DB;
            --accent-color: #E74C3C;
            --light-gray: #f8f9fa;
            --border-color: #dee2e6;
        }
        
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            font-family: 'Inter', sans-serif;
            padding: 1rem;
            margin: 0;
        }
        
        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.2);
            overflow: hidden;
            width: 100%;
            max-width: 1000px;
            display: flex;
            flex-direction: column;
        }
        
        @media (min-width: 992px) {
            .login-container {
                flex-direction: row;
                height: 600px;
            }
        }
        
        .login-left {
            padding: 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .login-right {
            background: var(--light-gray);
            padding: 2rem;
            flex: 1;
            display: flex;
            flex-direction: column;
            justify-content: center;
            border-left: 1px solid var(--border-color);
        }
        
        @media (max-width: 991px) {
            .login-right {
                border-left: none;
                border-top: 1px solid var(--border-color);
            }
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .logo-icon {
            font-size: 2.5rem;
            color: var(--accent-color);
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid var(--border-color);
        }
        
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        
        .input-group-text {
            background: var(--light-gray);
            border: 1px solid var(--border-color);
            padding: 0.75rem 1rem;
        }
        
        .btn-primary {
            background: var(--secondary-color);
            border: none;
            padding: 0.75rem 1.5rem;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: var(--primary-color);
            transform: translateY(-1px);
        }
        
        .download-title {
            text-align: center;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            font-size: 1.1rem;
            font-weight: 500;
        }
        
        .download-options {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        
        .download-option {
            text-decoration: none;
            color: var(--primary-color);
            background: white;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }
        
        .download-option:hover {
            border-color: var(--secondary-color);
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            color: var(--secondary-color);
        }
        
        .download-icon {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            display: block;
        }
        
        .qr-section {
            text-align: center;
            margin-top: 1rem;
        }
        
        .qr-code {
            max-width: 100px;
            margin: 0 auto;
            display: block;
        }
        
        .mobile-note {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
        
        .login-footer {
            text-align: center;
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: auto;
            padding-top: 1rem;
        }
    </style>
</head>
<body>
    <div class="login-container animate__animated animate__fadeIn">
        <div class="login-left">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h2>Agenda INGENES</h2>
                <p class="opacity-75">Sistema de Gestión de Citas</p>
            </div>
            
            <?php if(!empty($login_err)): ?>
                <div class="alert alert-danger animate__animated animate__shakeX mb-4">
                    <i class="bi bi-exclamation-triangle me-2"></i><?php echo $login_err; ?>
                </div>
            <?php endif; ?>

            <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                <div class="mb-4">
                    <label class="form-label">Usuario</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-person"></i>
                        </span>
                        <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Ingrese su usuario" autofocus>
                    </div>
                    <?php if(!empty($username_err)): ?>
                        <div class="invalid-feedback animate__animated animate__fadeIn"><?php echo $username_err; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="mb-4">
                    <label class="form-label">Contraseña</label>
                    <div class="input-group">
                        <span class="input-group-text">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input type="password" name="password" class="form-control <?php echo (!empty($password_err)) ? 'is-invalid' : ''; ?>" placeholder="Ingrese su contraseña">
                    </div>
                    <?php if(!empty($password_err)): ?>
                        <div class="invalid-feedback animate__animated animate__fadeIn"><?php echo $password_err; ?></div>
                    <?php endif; ?>
                </div>
                
                <div class="d-grid mb-4">
                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                    </button>
                </div>
            </form>
            
            <div class="login-footer">
                &copy; <?php echo date('Y'); ?> Agenda INGENES
            </div>
        </div>
        
        <div class="login-right">
            <div class="download-title">
                <i class="bi bi-download me-2"></i>Descarga nuestra aplicación
            </div>
            
            <div class="download-options">
                <a href="../public/downloads/AgendaINGENES.exe" class="download-option">
                    <i class="bi bi-windows download-icon"></i>
                    Windows
                </a>
                <a href="../public/downloads/AgendaINGENES.dmg" class="download-option">
                    <i class="bi bi-apple download-icon"></i>
                    macOS
                </a>
                <a href="../public/downloads/AgendaINGENES.apk" class="download-option">
                    <i class="bi bi-android2 download-icon"></i>
                    Android
                </a>
                <a href="https://apps.apple.com/app/agenda-ingenes" class="download-option">
                    <i class="bi bi-phone download-icon"></i>
                    iOS
                </a>
            </div>
            
            <div class="qr-section">
                <img src="../public/images/qr-code.png" alt="QR Code" class="qr-code">
                <p class="mobile-note">Escanea el código QR para descargar la app móvil</p>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
