<?php
// Verificar si el usuario está logueado
if (!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true) {
    header("location: login.php");
    exit;
}

// Obtener el nombre del archivo actual para marcar el enlace activo
$current_page = basename($_SERVER['PHP_SELF']);

// Redirigir a pacientes.php si el usuario no es admin y está intentando acceder a otras páginas
if (isset($_SESSION["rol"]) && $_SESSION["rol"] != "admin" && 
    !in_array($current_page, ['pacientes.php', 'login.php', 'logout.php', 'perfil.php']) && 
    $current_page != 'index.php') {
    header("location: pacientes.php");
    exit;
}

// Establecer la página de inicio según el rol
$home_page = (isset($_SESSION["rol"]) && $_SESSION["rol"] == "admin") ? 'index.php' : 'pacientes.php';
?>
<nav class="navbar navbar-expand-lg navbar-light sticky-top">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="<?php echo $home_page; ?>">
            <i class="bi bi-hospital text-primary me-2"></i>
            Clínica de Fertilidad
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <?php if(isset($_SESSION["rol"]) && $_SESSION["rol"] == "admin"): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'index.php' ? 'active' : ''; ?>" href="index.php">
                        <i class="bi bi-house-door me-1"></i>Inicio
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'pacientes.php' ? 'active' : ''; ?>" href="pacientes.php">
                        <i class="bi bi-people me-1"></i>Pacientes
                    </a>
                </li>
                
                <?php if(isset($_SESSION["rol"]) && $_SESSION["rol"] == "admin"): ?>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'groups.php' ? 'active' : ''; ?>" href="groups.php">
                        <i class="bi bi-calendar-check me-1"></i>Grupos
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'citas.php' ? 'active' : ''; ?>" href="citas.php">
                        <i class="bi bi-calendar2-week me-1"></i>Citas
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo $current_page == 'usuarios.php' ? 'active' : ''; ?>" href="usuarios.php">
                        <i class="bi bi-person-badge me-1"></i>Usuarios
                    </a>
                </li>
                <?php endif; ?>
            </ul>
            
            <div class="navbar-nav ms-auto">
                <div class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle d-flex align-items-center" href="#" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="bi bi-person-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION["name"] ?? $_SESSION["username"]); ?>
                        <?php if(isset($_SESSION["rol"]) && $_SESSION["rol"] == "admin"): ?>
                            <span class="badge bg-primary ms-2">Admin</span>
                        <?php endif; ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="perfil.php">
                            <i class="bi bi-person me-2"></i>Mi Perfil
                        </a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item text-danger" href="logout.php">
                            <i class="bi bi-box-arrow-right me-2"></i>Cerrar Sesión
                        </a></li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</nav>
