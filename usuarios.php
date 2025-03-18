<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true || $_SESSION["rol"] !== "admin"){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

// Inicializar variables
$success_msg = $error_msg = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$rol_filter = isset($_GET['rol']) ? $_GET['rol'] : '';

// Construir consulta SQL con filtros
$sql = "SELECT * FROM usuarios WHERE 1=1";

if($search !== '') {
    $sql .= " AND (nombre LIKE ? OR email LIKE ?)";
}

if($rol_filter !== '') {
    $sql .= " AND rol = ?";
}

$sql .= " ORDER BY nombre";

$stmt = mysqli_prepare($conn, $sql);

// Bind parameters si hay filtros
if($search !== '' && $rol_filter !== '') {
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "sss", $search_param, $search_param, $rol_filter);
} elseif($search !== '') {
    $search_param = "%$search%";
    mysqli_stmt_bind_param($stmt, "ss", $search_param, $search_param);
} elseif($rol_filter !== '') {
    mysqli_stmt_bind_param($stmt, "s", $rol_filter);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Usuarios - Clínica de Fertilidad</title>
    <?php include 'includes/modern-styles.php'; ?>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0">Usuarios</h1>
                    <p class="mb-0 opacity-75">Gestión de usuarios del sistema</p>
                </div>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newUserModal">
                    <i class="bi bi-person-plus me-2"></i>Nuevo Usuario
                </button>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <?php if($success_msg): ?>
        <div class="alert alert-success d-flex align-items-center" role="alert">
            <i class="bi bi-check-circle-fill me-2"></i>
            <?php echo $success_msg; ?>
        </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
        <div class="alert alert-danger d-flex align-items-center" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2"></i>
            <?php echo $error_msg; ?>
        </div>
        <?php endif; ?>

        <!-- Filtros -->
        <div class="card search-card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">
                            <i class="bi bi-search me-2"></i>Buscar usuarios
                        </label>
                        <input type="text" name="search" class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Buscar por nombre o email...">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="bi bi-person-badge me-2"></i>Rol
                        </label>
                        <select name="rol" class="form-select">
                            <option value="">Todos los roles</option>
                            <option value="admin" <?php echo $rol_filter === 'admin' ? 'selected' : ''; ?>>
                                Administrador
                            </option>
                            <option value="doctor" <?php echo $rol_filter === 'doctor' ? 'selected' : ''; ?>>
                                Doctor
                            </option>
                            <option value="staff" <?php echo $rol_filter === 'staff' ? 'selected' : ''; ?>>
                                Staff
                            </option>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Filtrar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de Usuarios -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Usuario</th>
                                <th>Email</th>
                                <th>Rol</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <?php echo htmlspecialchars($row['nombre']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <a href="mailto:<?php echo htmlspecialchars($row['email']); ?>" 
                                           class="text-decoration-none">
                                            <i class="bi bi-envelope me-1"></i>
                                            <?php echo htmlspecialchars($row['email']); ?>
                                        </a>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $row['rol'] == 'admin' ? 'danger' : 
                                                ($row['rol'] == 'doctor' ? 'success' : 'info'); 
                                        ?>">
                                            <?php echo ucfirst($row['rol']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <i class="bi bi-calendar3 me-1"></i>
                                        <?php echo date('d/m/Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" 
                                                    onclick="editUser(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <?php if($row['id'] != $_SESSION['id']): ?>
                                            <button type="button" class="btn btn-sm btn-light text-danger" 
                                                    onclick="deleteUser(<?php echo $row['id']; ?>)" 
                                                    title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="text-center py-4">
                                        <i class="bi bi-people text-muted d-block mb-2 fs-3"></i>
                                        <?php echo $search || $rol_filter ? 
                                            'No se encontraron usuarios con los criterios de búsqueda.' : 
                                            'No hay usuarios registrados aún.'; ?>
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nuevo/Editar Usuario -->
    <div class="modal fade" id="userModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Usuario</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="userForm" method="post">
                        <input type="hidden" name="action" value="create_user">
                        <input type="hidden" name="user_id" id="user_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Nombre</label>
                            <input type="text" name="nombre" id="nombre" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Contraseña</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="text-muted">Dejar en blanco para mantener la contraseña actual al editar</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Rol</label>
                            <select name="rol" id="rol" class="form-select" required>
                                <option value="admin">Administrador</option>
                                <option value="doctor">Doctor</option>
                                <option value="staff">Staff</option>
                            </select>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="userForm" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editUser(user) {
        document.getElementById('modalTitle').textContent = 'Editar Usuario';
        document.getElementById('userForm').action.value = 'edit_user';
        document.getElementById('user_id').value = user.id;
        document.getElementById('nombre').value = user.nombre;
        document.getElementById('email').value = user.email;
        document.getElementById('password').value = '';
        document.getElementById('rol').value = user.rol;
        
        new bootstrap.Modal(document.getElementById('userModal')).show();
    }

    function deleteUser(id) {
        if(confirm('¿Está seguro de que desea eliminar este usuario?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="delete_user">
                <input type="hidden" name="user_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Limpiar formulario al abrir modal de nuevo usuario
    document.getElementById('userModal').addEventListener('show.bs.modal', function(event) {
        if(event.relatedTarget && event.relatedTarget.getAttribute('data-bs-target') === '#newUserModal') {
            document.getElementById('modalTitle').textContent = 'Nuevo Usuario';
            document.getElementById('userForm').reset();
            document.getElementById('userForm').action.value = 'create_user';
            document.getElementById('user_id').value = '';
        }
    });
    </script>
</body>
</html>
