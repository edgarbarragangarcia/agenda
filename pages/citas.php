<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.html");
    exit;
}

require_once "../config/database.php";

// Inicializar variables
$success_msg = $error_msg = "";
$fecha_filter = isset($_GET['fecha']) ? $_GET['fecha'] : date('Y-m-d');
$doctor_filter = isset($_GET['doctor_id']) ? $_GET['doctor_id'] : '';

// Obtener lista de doctores
$sql_doctores = "SELECT id, nombre FROM usuarios WHERE rol = 'doctor' ORDER BY nombre";
$doctores = mysqli_query($conn, $sql_doctores);

// Construir consulta SQL con filtros
$sql = "SELECT c.*, 
        CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
        u.nombre as doctor_nombre,
        p.telefono as paciente_telefono,
        p.email as paciente_email
        FROM citas c 
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON c.doctor_id = u.id
        WHERE c.fecha = ?";

if($doctor_filter !== '') {
    $sql .= " AND c.doctor_id = ?";
}

$sql .= " ORDER BY c.hora";

$stmt = mysqli_prepare($conn, $sql);

if($doctor_filter !== '') {
    mysqli_stmt_bind_param($stmt, "si", $fecha_filter, $doctor_filter);
} else {
    mysqli_stmt_bind_param($stmt, "s", $fecha_filter);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Citas - Clínica de Fertilidad</title>
    <?php include '../includes/modern-styles.php'; ?>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0">Citas</h1>
                    <p class="mb-0 opacity-75">Gestión de citas médicas</p>
                </div>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                    <i class="bi bi-plus-circle me-2"></i>Nueva Cita
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
                            <i class="bi bi-calendar me-2"></i>Fecha
                        </label>
                        <input type="date" name="fecha" class="form-control" 
                               value="<?php echo $fecha_filter; ?>">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">
                            <i class="bi bi-person me-2"></i>Doctor
                        </label>
                        <select name="doctor_id" class="form-select">
                            <option value="">Todos los doctores</option>
                            <?php while($doctor = mysqli_fetch_assoc($doctores)): ?>
                            <option value="<?php echo $doctor['id']; ?>" 
                                    <?php echo $doctor_filter == $doctor['id'] ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($doctor['nombre']); ?>
                            </option>
                            <?php endwhile; ?>
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

        <!-- Lista de Citas -->
        <div class="card">
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead>
                            <tr>
                                <th>Hora</th>
                                <th>Paciente</th>
                                <th>Doctor</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Contacto</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo date('H:i', strtotime($row['hora'])); ?></strong>
                                    </td>
                                    <td>
                                        <div class="d-flex align-items-center">
                                            <div class="bg-light rounded-circle p-2 me-3">
                                                <i class="bi bi-person"></i>
                                            </div>
                                            <div>
                                                <?php echo htmlspecialchars($row['paciente_nombre']); ?>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <i class="bi bi-person-badge me-1"></i>
                                        <?php echo htmlspecialchars($row['doctor_nombre']); ?>
                                    </td>
                                    <td>
                                        <?php echo htmlspecialchars($row['tipo_consulta']); ?>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $row['estado'] == 'pendiente' ? 'warning' : 
                                                ($row['estado'] == 'confirmada' ? 'success' : 
                                                ($row['estado'] == 'cancelada' ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($row['estado']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <?php if($row['paciente_telefono']): ?>
                                            <a href="tel:<?php echo htmlspecialchars($row['paciente_telefono']); ?>" 
                                               class="btn btn-sm btn-light" title="Llamar">
                                                <i class="bi bi-telephone"></i>
                                            </a>
                                            <?php endif; ?>
                                            <?php if($row['paciente_email']): ?>
                                            <a href="mailto:<?php echo htmlspecialchars($row['paciente_email']); ?>" 
                                               class="btn btn-sm btn-light" title="Enviar email">
                                                <i class="bi bi-envelope"></i>
                                            </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="btn-group">
                                            <button type="button" class="btn btn-sm btn-light" 
                                                    onclick="editAppointment(<?php echo htmlspecialchars(json_encode($row)); ?>)" 
                                                    title="Editar">
                                                <i class="bi bi-pencil"></i>
                                            </button>
                                            <button type="button" class="btn btn-sm btn-light text-danger" 
                                                    onclick="deleteAppointment(<?php echo $row['id']; ?>)" 
                                                    title="Cancelar">
                                                <i class="bi bi-x-circle"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center py-4">
                                        <i class="bi bi-calendar-x text-muted d-block mb-2 fs-3"></i>
                                        No hay citas programadas para esta fecha.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function editAppointment(appointment) {
        // Implementar lógica de edición
    }

    function deleteAppointment(id) {
        if(confirm('¿Está seguro de que desea cancelar esta cita?')) {
            const form = document.createElement('form');
            form.method = 'POST';
            form.innerHTML = `
                <input type="hidden" name="action" value="cancel_appointment">
                <input type="hidden" name="appointment_id" value="${id}">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    }
    </script>
</body>
</html>
