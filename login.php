<?php
session_start();

if(isset($_SESSION["loggedin"]) && $_SESSION["loggedin"] === true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

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
                'password' => 'admin123',
                'name' => 'Administrador',
                'email' => 'admin@clinica.com',
                'rol' => 'admin'
            ],
            'usuario' => [
                'password' => 'usuario123',
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
            
            header("location: index.php");
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
    <?php include 'includes/modern-styles.php'; ?>
    <style>
        :root {
            --primary-color: #2C3E50;
            --secondary-color: #3498DB;
            --accent-color: #E74C3C;
        }
        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            font-family: 'Inter', sans-serif;
        }
        .login-container {
            background: white;
            border-radius: 1rem;
            box-shadow: 0 1rem 3rem rgba(0, 0, 0, 0.2);
            overflow: hidden;
            margin: 2rem auto;
            max-width: 420px;
            width: 90%;
        }
        .login-header {
            background: var(--primary-color);
            color: white;
            padding: 2.5rem 2rem;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        .login-header::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: var(--accent-color);
        }
        .login-body {
            padding: 2.5rem 2rem;
        }
        .login-footer {
            background: #f8f9fa;
            padding: 1rem;
            text-align: center;
            font-size: 0.875rem;
            color: #6c757d;
            border-top: 1px solid #eee;
        }
        .form-control {
            padding: 0.75rem 1rem;
            border-radius: 0.5rem;
            border: 1px solid #dee2e6;
        }
        .form-control:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }
        .input-group-text {
            background: #f8f9fa;
            border: 1px solid #dee2e6;
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
        .logo-icon {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            color: var(--accent-color);
        }
        .download-section {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
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
            grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }
        .download-option {
            text-decoration: none;
            color: var(--primary-color);
            background: #f8f9fa;
            padding: 1rem;
            border-radius: 0.5rem;
            text-align: center;
            transition: all 0.3s ease;
            border: 1px solid #eee;
        }
        .download-option:hover {
            background: white;
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
            margin-top: 1.5rem;
        }
        .qr-code {
            max-width: 120px;
            margin: 0 auto;
            display: block;
        }
        .mobile-note {
            font-size: 0.875rem;
            color: #6c757d;
            margin-top: 0.5rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-container animate__animated animate__fadeInUp">
            <div class="login-header">
                <div class="logo-icon">
                    <i class="bi bi-calendar-check"></i>
                </div>
                <h2 class="mb-2">Agenda INGENES</h2>
                <p class="mb-0 opacity-75">Sistema de Gestión de Citas</p>
            </div>
            
            <div class="login-body">
                <?php if(!empty($login_err)): ?>
                    <div class="alert alert-danger animate__animated animate__shakeX">
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
                            <input type="text" name="username" class="form-control <?php echo (!empty($username_err)) ? 'is-invalid' : ''; ?>" value="<?php echo $username; ?>" placeholder="Ingrese su usuario">
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
                    
                    <div class="d-grid">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Iniciar Sesión
                        </button>
                    </div>
                </form>

                <div class="download-section">
                    <div class="download-title">
                        <i class="bi bi-download me-2"></i>Descarga nuestra aplicación
                    </div>
                    <div class="download-options">
                        <a href="public/downloads/AgendaINGENES.exe" class="download-option">
                            <i class="bi bi-windows download-icon"></i>
                            Windows
                        </a>
                        <a href="public/downloads/AgendaINGENES.dmg" class="download-option">
                            <i class="bi bi-apple download-icon"></i>
                            macOS
                        </a>
                        <a href="public/downloads/AgendaINGENES.apk" class="download-option">
                            <i class="bi bi-android2 download-icon"></i>
                            Android
                        </a>
                        <a href="https://apps.apple.com/app/agenda-ingenes" class="download-option">
                            <i class="bi bi-phone download-icon"></i>
                            iOS
                        </a>
                    </div>
                    <div class="qr-section">
                        <img src="public/images/qr-code.png" alt="QR Code" class="qr-code">
                        <p class="mobile-note">Escanea el código QR para descargar la app móvil</p>
                    </div>
                </div>
            </div>
            
            <div class="login-footer">
                &copy; <?php echo date('Y'); ?> Agenda INGENES
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
