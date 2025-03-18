<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: index.php");
    exit;
}

require_once "config/database.php";

$success_msg = $error_msg = "";

// Procesar el formulario de nueva cita
if($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST["action"]) && $_POST["action"] == "create"){
    $paciente_id = trim($_POST["paciente_id"]);
    $doctor_id = trim($_POST["doctor_id"]);
    $fecha = trim($_POST["fecha"]);
    $hora = trim($_POST["hora"]);
    $tipo_consulta = trim($_POST["tipo_consulta"]);
    $notas = trim($_POST["notas"]);

    $sql = "INSERT INTO citas (paciente_id, doctor_id, fecha, hora, tipo_consulta, notas) VALUES (?, ?, ?, ?, ?, ?)";
    
    if($stmt = mysqli_prepare($conn, $sql)){
        mysqli_stmt_bind_param($stmt, "iissss", $paciente_id, $doctor_id, $fecha, $hora, $tipo_consulta, $notas);
        
        if(mysqli_stmt_execute($stmt)){
            $success_msg = "Cita programada exitosamente.";
        } else{
            $error_msg = "Error al programar la cita.";
        }
        mysqli_stmt_close($stmt);
    }
}

// Obtener lista de citas
$sql = "SELECT c.*, 
        CONCAT(p.apellido, ', ', p.nombre) as paciente_nombre,
        CONCAT(u.nombre) as doctor_nombre
        FROM citas c
        JOIN pacientes p ON c.paciente_id = p.id
        JOIN usuarios u ON c.doctor_id = u.id
        WHERE c.fecha >= CURDATE()
        ORDER BY c.fecha, c.hora";
$result = mysqli_query($conn, $sql);

// Obtener lista de doctores para el formulario
$sql_doctors = "SELECT id, nombre FROM usuarios WHERE rol = 'doctor'";
$doctors = mysqli_query($conn, $sql_doctors);

// Obtener lista de pacientes para el formulario
$sql_patients = "SELECT id, CONCAT(apellido, ', ', nombre) as nombre_completo FROM pacientes ORDER BY apellido, nombre";
$patients = mysqli_query($conn, $sql_patients);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Citas - Clínica de Fertilidad</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="#">Clínica de Fertilidad</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.php">Dashboard</a>
                    </li>
                    <?php if($_SESSION["rol"] == "admin"): ?>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">Usuarios</a>
                    </li>
                    <?php endif; ?>
                    <li class="nav-item">
                        <a class="nav-link active" href="appointments.php">Citas</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="patients.php">Pacientes</a>
                    </li>
                </ul>
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Cerrar Sesión</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Gestión de Citas</h2>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                <i class="bi bi-plus-circle"></i> Nueva Cita
            </button>
        </div>

        <?php if($success_msg): ?>
        <div class="alert alert-success"><?php echo $success_msg; ?></div>
        <?php endif; ?>

        <?php if($error_msg): ?>
        <div class="alert alert-danger"><?php echo $error_msg; ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Paciente</th>
                        <th>Doctor</th>
                        <th>Tipo de Consulta</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while($row = mysqli_fetch_assoc($result)): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                        <td><?php echo htmlspecialchars($row['hora']); ?></td>
                        <td><?php echo htmlspecialchars($row['paciente_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['doctor_nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo_consulta']); ?></td>
                        <td>
                            <span class="badge bg-<?php 
                                echo $row['estado'] == 'confirmada' ? 'success' : 
                                    ($row['estado'] == 'pendiente' ? 'warning' : 
                                    ($row['estado'] == 'cancelada' ? 'danger' : 'info')); 
                            ?>">
                                <?php echo ucfirst($row['estado']); ?>
                            </span>
                        </td>
                        <td>
                            <a href="view_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-info btn-sm">
                                <i class="bi bi-eye"></i>
                            </a>
                            <a href="edit_appointment.php?id=<?php echo $row['id']; ?>" class="btn btn-warning btn-sm">
                                <i class="bi bi-pencil"></i>
                            </a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal para nueva cita -->
    <div class="modal fade" id="newAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" method="post">
                        <input type="hidden" name="action" value="create">
                        <div class="mb-3">
                            <label class="form-label">Paciente</label>
                            <select name="paciente_id" class="form-control" required>
                                <option value="">Seleccione un paciente</option>
                                <?php while($patient = mysqli_fetch_assoc($patients)): ?>
                                <option value="<?php echo $patient['id']; ?>">
                                    <?php echo htmlspecialchars($patient['nombre_completo']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Doctor</label>
                            <select name="doctor_id" class="form-control" required>
                                <option value="">Seleccione un doctor</option>
                                <?php while($doctor = mysqli_fetch_assoc($doctors)): ?>
                                <option value="<?php echo $doctor['id']; ?>">
                                    <?php echo htmlspecialchars($doctor['nombre']); ?>
                                </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Fecha</label>
                            <input type="date" name="fecha" class="form-control" required min="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Hora</label>
                            <input type="time" name="hora" class="form-control" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipo de Consulta</label>
                            <select name="tipo_consulta" class="form-control" required>
                                <option value="">Seleccione el tipo</option>
                                <option value="Primera Consulta">Primera Consulta</option>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="Control">Control</option>
                                <option value="Procedimiento">Procedimiento</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Notas</label>
                            <textarea name="notas" class="form-control" rows="3"></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary">Guardar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
