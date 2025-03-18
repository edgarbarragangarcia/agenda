<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: login.php");
    exit;
}

require_once "config/database.php";
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inicio - Clínica de Fertilidad</title>
    <?php include 'includes/modern-styles.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.css"/>
</head>
<body>
    <?php include 'includes/navbar.php'; ?>

    <div class="page-header animate__animated animate__fadeIn">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-md-8">
                    <h1 class="mb-0 fw-bold">Bienvenido/a, <?php echo htmlspecialchars($_SESSION["nombre"]); ?></h1>
                    <p class="mb-0 opacity-75">Panel de Control - Clínica de Fertilidad</p>
                </div>
                <div class="col-md-4 text-md-end">
                    <p class="mb-0">
                        <i class="bi bi-calendar3 me-2"></i>
                        <?php echo date("d M, Y"); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="container py-4">
        <!-- Estadísticas -->
        <div class="row">
            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.1s">
                <div class="stats-card">
                    <div class="stats-icon mb-3">
                        <i class="bi bi-calendar-check"></i>
                    </div>
                    <h6 class="text-muted">Citas Hoy</h6>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM citas WHERE fecha = CURDATE()";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h3><?php echo $row['total']; ?></h3>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.2s">
                <div class="stats-card">
                    <div class="stats-icon mb-3">
                        <i class="bi bi-people"></i>
                    </div>
                    <h6 class="text-muted">Pacientes</h6>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM pacientes";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h3><?php echo $row['total']; ?></h3>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.3s">
                <div class="stats-card">
                    <div class="stats-icon mb-3">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <h6 class="text-muted">Doctores</h6>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM usuarios WHERE rol = 'doctor'";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h3><?php echo $row['total']; ?></h3>
                </div>
            </div>

            <div class="col-xl-3 col-md-6 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.4s">
                <div class="stats-card">
                    <div class="stats-icon mb-3">
                        <i class="bi bi-building"></i>
                    </div>
                    <h6 class="text-muted">Sucursales</h6>
                    <?php
                    $sql = "SELECT COUNT(*) as total FROM sucursales";
                    $result = mysqli_query($conn, $sql);
                    $row = mysqli_fetch_assoc($result);
                    ?>
                    <h3><?php echo $row['total']; ?></h3>
                </div>
            </div>
        </div>

        <!-- Próximas Citas -->
        <div class="row">
            <div class="col-lg-8 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.5s">
                <div class="card">
                    <div class="card-header d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">Próximas Citas</h5>
                        <a href="citas.php" class="btn btn-sm btn-primary">
                            <i class="bi bi-calendar-plus me-1"></i> Ver Todas
                        </a>
                    </div>
                    <div class="table-responsive">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th>Paciente</th>
                                    <th>Fecha</th>
                                    <th>Hora</th>
                                    <th>Doctor</th>
                                    <th>Estado</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT c.*, 
                                    CONCAT(p.nombre, ' ', p.apellido) as paciente_nombre,
                                    u.nombre as doctor_nombre
                                    FROM citas c 
                                    JOIN pacientes p ON c.paciente_id = p.id
                                    JOIN usuarios u ON c.doctor_id = u.id
                                    WHERE c.fecha >= CURDATE()
                                    ORDER BY c.fecha, c.hora
                                    LIMIT 5";
                                $result = mysqli_query($conn, $sql);
                                while($row = mysqli_fetch_assoc($result)):
                                ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['paciente_nombre']); ?></td>
                                    <td><?php echo date('d M', strtotime($row['fecha'])); ?></td>
                                    <td><?php echo date('H:i', strtotime($row['hora'])); ?></td>
                                    <td><?php echo htmlspecialchars($row['doctor_nombre']); ?></td>
                                    <td>
                                        <span class="badge bg-<?php 
                                            echo $row['estado'] == 'pendiente' ? 'warning' : 
                                                ($row['estado'] == 'confirmada' ? 'success' : 
                                                ($row['estado'] == 'cancelada' ? 'danger' : 'info')); 
                                        ?>">
                                            <?php echo ucfirst($row['estado']); ?>
                                        </span>
                                    </td>
                                </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="col-lg-4 mb-4 animate__animated animate__fadeInUp" style="animation-delay: 0.6s">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Actividad Reciente</h5>
                    </div>
                    <div class="card-body p-0">
                        <div class="list-group list-group-flush">
                            <?php
                            // Aquí podrías agregar registros de actividad reciente
                            $activities = [
                                ['icon' => 'bi-person-plus', 'color' => 'success', 'text' => 'Nuevo paciente registrado'],
                                ['icon' => 'bi-calendar-check', 'color' => 'primary', 'text' => 'Cita confirmada'],
                                ['icon' => 'bi-clock-history', 'color' => 'warning', 'text' => 'Cita reprogramada'],
                                ['icon' => 'bi-envelope', 'color' => 'info', 'text' => 'Recordatorio enviado']
                            ];
                            foreach($activities as $activity):
                            ?>
                            <div class="list-group-item border-0 d-flex align-items-center px-3 py-3">
                                <div class="me-3">
                                    <span class="avatar-sm bg-<?php echo $activity['color']; ?>-light rounded-circle d-flex align-items-center justify-content-center">
                                        <i class="bi <?php echo $activity['icon']; ?> text-<?php echo $activity['color']; ?>"></i>
                                    </span>
                                </div>
                                <div>
                                    <p class="mb-0"><?php echo $activity['text']; ?></p>
                                    <small class="text-muted">Hace 2 horas</small>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/animate.css@4.1.1/animate.min.js"></script>
    <style>
        .avatar-sm {
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14px;
            font-weight: 600;
            color: var(--primary-color);
        }
        
        .bg-primary-light {
            background-color: rgba(67, 97, 238, 0.1);
        }
        
        .bg-success-light {
            background-color: rgba(46, 196, 182, 0.1);
        }
        
        .bg-warning-light {
            background-color: rgba(255, 159, 28, 0.1);
        }
        
        .bg-info-light {
            background-color: rgba(58, 134, 255, 0.1);
        }
        
        .text-success {
            color: var(--success-color) !important;
        }
        
        .text-primary {
            color: var(--primary-color) !important;
        }
        
        .text-warning {
            color: var(--warning-color) !important;
        }
        
        .text-info {
            color: var(--info-color) !important;
        }
    </style>
</body>
</html>
