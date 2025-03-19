<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../index.html");
    exit;
}

require_once "../config/database.php";

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
    <title>Gestión de Citas - Agenda INGENES</title>
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css" rel="stylesheet">
    <!-- Inter Font -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <!-- Unified Styles -->
    <link href="../public/css/unified-style.css" rel="stylesheet">
    <!-- Animation Library -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container">
            <a class="navbar-brand" href="dashboard.html">Agenda INGENES</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="dashboard.html">
                            <i class="bi bi-house-door"></i> Inicio
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="appointments.php">
                            <i class="bi bi-calendar-event"></i> Citas
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="pacientes.php">
                            <i class="bi bi-people"></i> Pacientes
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="groups.php">
                            <i class="bi bi-people-fill"></i> Grupos
                        </a>
                    </li>
                </ul>
                <div class="d-flex">
                    <button class="btn btn-success me-2" data-bs-toggle="modal" data-bs-target="#newAppointmentModal">
                        <i class="bi bi-plus-circle"></i> Nueva Cita
                    </button>
                    <a href="logout.php" class="btn btn-outline-light">
                        <i class="bi bi-box-arrow-right"></i> Salir
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if(!empty($success_msg)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php echo $success_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        
        <?php if(!empty($error_msg)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php echo $error_msg; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card shadow-sm">
            <div class="card-header bg-white">
                <h5 class="card-title mb-0">Citas Programadas</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead>
                            <tr>
                                <th>Paciente</th>
                                <th>Doctor</th>
                                <th>Fecha</th>
                                <th>Hora</th>
                                <th>Tipo</th>
                                <th>Estado</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if(mysqli_num_rows($result) > 0): ?>
                                <?php while($row = mysqli_fetch_assoc($result)): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row["paciente_nombre"]); ?></td>
                                        <td><?php echo htmlspecialchars($row["doctor_nombre"]); ?></td>
                                        <td><?php echo date("d/m/Y", strtotime($row["fecha"])); ?></td>
                                        <td><?php echo date("H:i", strtotime($row["hora"])); ?></td>
                                        <td><?php echo htmlspecialchars($row["tipo_consulta"]); ?></td>
                                        <td>
                                            <?php if($row["estado"] == "confirmada"): ?>
                                                <span class="badge bg-success">Confirmada</span>
                                            <?php elseif($row["estado"] == "cancelada"): ?>
                                                <span class="badge bg-danger">Cancelada</span>
                                            <?php else: ?>
                                                <span class="badge bg-warning">Pendiente</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="btn-group btn-group-sm">
                                                <button type="button" class="btn btn-outline-primary">
                                                    <i class="bi bi-pencil"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-success">
                                                    <i class="bi bi-check-circle"></i>
                                                </button>
                                                <button type="button" class="btn btn-outline-danger">
                                                    <i class="bi bi-x-circle"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="7" class="text-center">No hay citas programadas.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal Nueva Cita -->
    <div class="modal fade" id="newAppointmentModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Nueva Cita</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <form action="appointments.php" method="post">
                        <input type="hidden" name="action" value="create">
                        
                        <div class="mb-3">
                            <label for="paciente_id" class="form-label">Paciente</label>
                            <select class="form-select" id="paciente_id" name="paciente_id" required>
                                <option value="">Seleccione un paciente</option>
                                <?php while($patient = mysqli_fetch_assoc($patients)): ?>
                                    <option value="<?php echo $patient['id']; ?>">
                                        <?php echo htmlspecialchars($patient['nombre_completo']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="doctor_id" class="form-label">Doctor</label>
                            <select class="form-select" id="doctor_id" name="doctor_id" required>
                                <option value="">Seleccione un doctor</option>
                                <?php while($doctor = mysqli_fetch_assoc($doctors)): ?>
                                    <option value="<?php echo $doctor['id']; ?>">
                                        <?php echo htmlspecialchars($doctor['nombre']); ?>
                                    </option>
                                <?php endwhile; ?>
                            </select>
                        </div>
                        
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label for="fecha" class="form-label">Fecha</label>
                                <input type="date" class="form-control" id="fecha" name="fecha" required>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="hora" class="form-label">Hora</label>
                                <input type="time" class="form-control" id="hora" name="hora" required>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label for="tipo_consulta" class="form-label">Tipo de Consulta</label>
                            <select class="form-select" id="tipo_consulta" name="tipo_consulta" required>
                                <option value="">Seleccione un tipo</option>
                                <option value="Primera vez">Primera vez</option>
                                <option value="Seguimiento">Seguimiento</option>
                                <option value="Procedimiento">Procedimiento</option>
                                <option value="Control">Control</option>
                            </select>
                        </div>
                        
                        <div class="mb-3">
                            <label for="notas" class="form-label">Notas</label>
                            <textarea class="form-control" id="notas" name="notas" rows="3"></textarea>
                        </div>
                        
                        <div class="text-end">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                            <button type="submit" class="btn btn-primary">Guardar Cita</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
