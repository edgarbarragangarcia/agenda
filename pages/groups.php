<?php
session_start();

if(!isset($_SESSION["loggedin"]) || $_SESSION["loggedin"] !== true){
    header("location: ../index.html");
    exit;
}

require_once "../config/database.php";

// Inicializar variables
$success_msg = $error_msg = "";
$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sucursal_filter = isset($_GET['sucursal_filter']) ? (int)$_GET['sucursal_filter'] : '';

// Obtener lista de sucursales
$sucursales = mysqli_query($conn, "SELECT * FROM sucursales ORDER BY nombre");

// Obtener lista de médicos
$medicos = mysqli_query($conn, "SELECT id, nombre FROM usuarios WHERE rol = 'doctor' ORDER BY nombre");

// Construir consulta SQL base
$sql = "SELECT g.*, s.nombre as sucursal_nombre, 
        GROUP_CONCAT(DISTINCT h.dia_semana) as dias_semana,
        MIN(h.hora_inicio) as hora_inicio,
        MAX(h.hora_fin) as hora_fin,
        GROUP_CONCAT(DISTINCT CONCAT(u.id, ':', u.nombre) SEPARATOR '|') as medicos
        FROM grupos g 
        LEFT JOIN sucursales s ON g.sucursal_id = s.id
        LEFT JOIN horarios_grupo h ON g.id = h.grupo_id
        LEFT JOIN medicos_grupo mg ON g.id = mg.grupo_id
        LEFT JOIN usuarios u ON mg.usuario_id = u.id
        WHERE 1=1";

$params = array();
$types = "";

// Agregar condiciones de búsqueda
if($search !== '') {
    $sql .= " AND (g.nombre LIKE ? OR g.descripcion LIKE ?)";
    $searchParam = "%$search%";
    $params[] = $searchParam;
    $params[] = $searchParam;
    $types .= "ss";
}

// Agregar filtro de sucursal
if($sucursal_filter !== '' && $sucursal_filter > 0) {
    $sql .= " AND g.sucursal_id = ?";
    $params[] = $sucursal_filter;
    $types .= "i";
}

$sql .= " GROUP BY g.id ORDER BY g.nombre";

// Preparar y ejecutar la consulta
$stmt = mysqli_prepare($conn, $sql);

if (!empty($params)) {
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}

mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

// Procesar acciones POST
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'create_group':
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                $sucursal_id = $_POST['sucursal_id'];
                $dias_semana = isset($_POST['dias_semana']) ? $_POST['dias_semana'] : [];
                $hora_inicio = $_POST['hora_inicio'];
                $hora_fin = $_POST['hora_fin'];
                $medicos_seleccionados = isset($_POST['medicos']) ? $_POST['medicos'] : [];

                // Insertar grupo
                $sql = "INSERT INTO grupos (nombre, descripcion, sucursal_id) VALUES (?, ?, ?)";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssi", $nombre, $descripcion, $sucursal_id);
                    if (mysqli_stmt_execute($stmt)) {
                        $grupo_id = mysqli_insert_id($conn);
                        
                        // Insertar horarios
                        foreach ($dias_semana as $dia) {
                            $sql = "INSERT INTO horarios_grupo (grupo_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)";
                            if ($stmt_horario = mysqli_prepare($conn, $sql)) {
                                mysqli_stmt_bind_param($stmt_horario, "iiss", $grupo_id, $dia, $hora_inicio, $hora_fin);
                                mysqli_stmt_execute($stmt_horario);
                                mysqli_stmt_close($stmt_horario);
                            }
                        }

                        // Procesar médicos
                        if (isset($_POST['medicos_data'])) {
                            $medicos_data = json_decode($_POST['medicos_data'], true);
                            
                            // Eliminar asignaciones existentes
                            mysqli_query($conn, "DELETE FROM medicos_grupo WHERE grupo_id = $grupo_id");
                            
                            // Insertar nuevas asignaciones con tipo de laboratorio
                            foreach ($medicos_data as $medico) {
                                $medico_id = (int)$medico['id'];
                                $tipo_lab = mysqli_real_escape_string($conn, $medico['tipo_lab']);
                                mysqli_query($conn, "INSERT INTO medicos_grupo (grupo_id, usuario_id, tipo_lab) 
                                              VALUES ($grupo_id, $medico_id, '$tipo_lab')");
                            }
                        }
                        
                        $success_msg = "Grupo creado exitosamente.";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error_msg = "Error al crear el grupo.";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'edit_group':
                $grupo_id = $_POST['grupo_id'];
                $nombre = trim($_POST['nombre']);
                $descripcion = trim($_POST['descripcion']);
                $sucursal_id = $_POST['sucursal_id'];
                $dias_semana = isset($_POST['dias_semana']) ? $_POST['dias_semana'] : [];
                $hora_inicio = $_POST['hora_inicio'];
                $hora_fin = $_POST['hora_fin'];
                $medicos_seleccionados = isset($_POST['medicos']) ? $_POST['medicos'] : [];

                // Actualizar grupo
                $sql = "UPDATE grupos SET nombre = ?, descripcion = ?, sucursal_id = ? WHERE id = ?";
                if ($stmt = mysqli_prepare($conn, $sql)) {
                    mysqli_stmt_bind_param($stmt, "ssii", $nombre, $descripcion, $sucursal_id, $grupo_id);
                    if (mysqli_stmt_execute($stmt)) {
                        // Eliminar horarios existentes
                        mysqli_query($conn, "DELETE FROM horarios_grupo WHERE grupo_id = $grupo_id");
                        
                        // Insertar nuevos horarios
                        foreach ($dias_semana as $dia) {
                            $sql = "INSERT INTO horarios_grupo (grupo_id, dia_semana, hora_inicio, hora_fin) VALUES (?, ?, ?, ?)";
                            if ($stmt_horario = mysqli_prepare($conn, $sql)) {
                                mysqli_stmt_bind_param($stmt_horario, "iiss", $grupo_id, $dia, $hora_inicio, $hora_fin);
                                mysqli_stmt_execute($stmt_horario);
                                mysqli_stmt_close($stmt_horario);
                            }
                        }

                        // Procesar médicos
                        if (isset($_POST['medicos_data'])) {
                            $medicos_data = json_decode($_POST['medicos_data'], true);
                            
                            // Eliminar asignaciones existentes
                            mysqli_query($conn, "DELETE FROM medicos_grupo WHERE grupo_id = $grupo_id");
                            
                            // Insertar nuevas asignaciones con tipo de laboratorio
                            foreach ($medicos_data as $medico) {
                                $medico_id = (int)$medico['id'];
                                $tipo_lab = mysqli_real_escape_string($conn, $medico['tipo_lab']);
                                mysqli_query($conn, "INSERT INTO medicos_grupo (grupo_id, usuario_id, tipo_lab) 
                                              VALUES ($grupo_id, $medico_id, '$tipo_lab')");
                            }
                        }
                        
                        $success_msg = "Grupo actualizado exitosamente.";
                        header("Location: " . $_SERVER['PHP_SELF']);
                        exit();
                    } else {
                        $error_msg = "Error al actualizar el grupo.";
                    }
                    mysqli_stmt_close($stmt);
                }
                break;

            case 'delete_group':
                $grupo_id = $_POST['grupo_id'];
                if (mysqli_query($conn, "DELETE FROM grupos WHERE id = $grupo_id")) {
                    $success_msg = "Grupo eliminado exitosamente.";
                } else {
                    $error_msg = "Error al eliminar el grupo.";
                }
                break;
        }
    }
}

?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Grupos - Clínica de Fertilidad</title>
    <?php include '../includes/modern-styles.php'; ?>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/fullcalendar@5.11.3/main.min.css">
    <style>
        .day-selector {
            display: grid;
            grid-template-columns: repeat(7, 1fr);
            gap: 5px;
            margin-bottom: 1rem;
        }
        .day-item {
            padding: 10px;
            text-align: center;
            border: 1px solid var(--secondary-color);
            border-radius: var(--border-radius);
            cursor: pointer;
            transition: all 0.3s;
        }
        .day-item:hover {
            background-color: var(--light-color);
        }
        .day-item.selected {
            background-color: var(--primary-color);
            color: white;
            border-color: var(--primary-color);
        }
        .schedule-card {
            border: 1px solid var(--secondary-color);
            border-radius: var(--border-radius);
            padding: 10px;
            margin-bottom: 10px;
        }
        .schedule-card .time {
            font-weight: bold;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <?php include '../includes/navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="display-6 mb-0">Gestión de Grupos</h1>
                    <p class="mb-0 opacity-75">Administra los grupos y sus horarios</p>
                </div>
                <button type="button" class="btn btn-light" data-bs-toggle="modal" data-bs-target="#groupModal">
                    <i class="bi bi-plus-circle me-2"></i>Nuevo Grupo
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

        <!-- Buscador -->
        <div class="card search-card mb-4">
            <div class="card-body">
                <form method="get" class="row g-3" id="searchForm">
                    <div class="col-md-5">
                        <label class="form-label">
                            <i class="bi bi-search me-2"></i>Buscar por nombre o descripción
                        </label>
                        <input type="text" 
                               name="search" 
                               class="form-control" 
                               value="<?php echo htmlspecialchars($search); ?>" 
                               placeholder="Buscar grupos...">
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">
                            <i class="bi bi-building me-2"></i>Filtrar por sucursal
                        </label>
                        <select name="sucursal_filter" class="form-select" onchange="this.form.submit()">
                            <option value="">Todas las sucursales</option>
                            <?php 
                            mysqli_data_seek($sucursales, 0);
                            while($sucursal = mysqli_fetch_assoc($sucursales)): 
                            ?>
                            <option value="<?php echo $sucursal['id']; ?>" 
                                    <?php echo ($sucursal_filter !== '' && $sucursal_filter == $sucursal['id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($sucursal['nombre']); ?>
                            </option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">
                            <i class="bi bi-search me-2"></i>Buscar
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Lista de grupos -->
        <div class="row">
            <?php if(mysqli_num_rows($result) > 0): ?>
                <?php while($row = mysqli_fetch_assoc($result)): 
                    // Preparar los datos del grupo para JavaScript
                    $grupoData = array(
                        'id' => $row['id'],
                        'nombre' => $row['nombre'],
                        'descripcion' => $row['descripcion'],
                        'sucursal_id' => $row['sucursal_id'],
                        'hora_inicio' => $row['hora_inicio'],
                        'hora_fin' => $row['hora_fin'],
                        'dias_semana' => $row['dias_semana'],
                        'medicos' => $row['medicos']
                    );
                ?>
                <div class="col-md-6 mb-4">
                    <div class="card mb-3">
                        <div class="card-body">
                            <div class="d-flex justify-content-between align-items-start mb-2">
                                <h5 class="card-title mb-0"><?php echo htmlspecialchars($row['nombre']); ?></h5>
                                <div class="btn-group">
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-primary" 
                                            data-grupo='<?php echo json_encode($grupoData); ?>'
                                            onclick="editarGrupo(this)">
                                        <i class="bi bi-pencil"></i> Editar
                                    </button>
                                    <button type="button" 
                                            class="btn btn-sm btn-outline-danger" 
                                            onclick="eliminarGrupo(<?php echo $row['id']; ?>)">
                                        <i class="bi bi-trash"></i> Eliminar
                                    </button>
                                </div>
                            </div>
                            <p class="card-text text-muted small mb-2"><?php echo htmlspecialchars($row['descripcion']); ?></p>
                            <p class="card-text small mb-2">
                                <i class="bi bi-building me-1"></i>
                                <?php echo htmlspecialchars($row['sucursal_nombre']); ?>
                            </p>
                            <div class="mb-2">
                                <?php
                                if ($row['dias_semana']) {
                                    $dias = explode(',', $row['dias_semana']);
                                    $dias_semana_nombres = [
                                        0 => 'Domingo',
                                        1 => 'Lunes',
                                        2 => 'Martes',
                                        3 => 'Miércoles',
                                        4 => 'Jueves',
                                        5 => 'Viernes',
                                        6 => 'Sábado'
                                    ];
                                    foreach ($dias as $dia) {
                                        echo '<span class="badge bg-primary me-1">' . $dias_semana_nombres[$dia] . '</span>';
                                    }
                                }
                                ?>
                            </div>
                            <p class="card-text small mb-2">
                                <i class="bi bi-clock me-1"></i>
                                <?php 
                                if ($row['hora_inicio'] && $row['hora_fin']) {
                                    echo date('h:i A', strtotime($row['hora_inicio'])) . ' - ' . date('h:i A', strtotime($row['hora_fin']));
                                }
                                ?>
                            </p>
                            <div class="mt-2">
                                <p class="card-text small mb-1"><strong>Médicos Asignados:</strong></p>
                                <?php
                                if ($row['medicos']) {
                                    $medicos_array = explode('|', $row['medicos']);
                                    foreach ($medicos_array as $medico) {
                                        list($id, $nombre) = explode(':', $medico);
                                        echo '<span class="badge bg-info me-1">' . htmlspecialchars($nombre) . '</span>';
                                    }
                                } else {
                                    echo '<span class="text-muted small">No hay médicos asignados</span>';
                                }
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            <?php else: ?>
                <div class="col-12">
                    <div class="alert alert-info d-flex align-items-center">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <?php echo $search || $sucursal_filter ? 
                            'No se encontraron grupos con los filtros seleccionados.' : 
                            'No hay grupos creados aún. Haga clic en "Nuevo Grupo" para crear uno.'; ?>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Modal para nuevo/editar grupo -->
    <div class="modal fade" id="groupModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Nuevo Grupo</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <form id="groupForm" method="post">
                        <input type="hidden" name="action" value="create_group">
                        <input type="hidden" name="grupo_id" id="grupo_id">
                        
                        <div class="row g-3">
                            <div class="col-md-8">
                                <label class="form-label">Nombre del Grupo</label>
                                <input type="text" name="nombre" id="nombre" class="form-control" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label">Sucursal</label>
                                <select name="sucursal_id" id="sucursal_id" class="form-select" required>
                                    <?php 
                                    mysqli_data_seek($sucursales, 0);
                                    while($sucursal = mysqli_fetch_assoc($sucursales)): 
                                    ?>
                                    <option value="<?php echo $sucursal['id']; ?>">
                                        <?php echo htmlspecialchars($sucursal['nombre']); ?>
                                    </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Descripción</label>
                                <textarea name="descripcion" id="descripcion" class="form-control" rows="2"></textarea>
                            </div>
                            <div class="col-12">
                                <label class="form-label">Días de la Semana</label>
                                <div class="day-selector" id="daySelector">
                                    <div class="day-item" data-day="0">Dom</div>
                                    <div class="day-item" data-day="1">Lun</div>
                                    <div class="day-item" data-day="2">Mar</div>
                                    <div class="day-item" data-day="3">Mié</div>
                                    <div class="day-item" data-day="4">Jue</div>
                                    <div class="day-item" data-day="5">Vie</div>
                                    <div class="day-item" data-day="6">Sáb</div>
                                </div>
                                <div id="diasSeleccionados"></div>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora de Inicio</label>
                                <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label">Hora de Fin</label>
                                <input type="time" name="hora_fin" id="hora_fin" class="form-control" required>
                            </div>
                            <div class="col-12">
                                <div class="mb-3">
                                    <label class="form-label">Médicos asignados</label>
                                    <div class="card">
                                        <div class="card-body">
                                            <div class="mb-3">
                                                <input type="text" class="form-control" id="doctorSearch" 
                                                       placeholder="Buscar médico..." onkeyup="filterDoctors()">
                                            </div>
                                            <div class="doctor-list" style="max-height: 300px; overflow-y: auto;">
                                                <?php 
                                                mysqli_data_seek($medicos, 0);
                                                while($medico = mysqli_fetch_assoc($medicos)): 
                                                ?>
                                                <div class="doctor-item mb-2 p-2 border rounded">
                                                    <div class="d-flex align-items-center justify-content-between">
                                                        <div class="form-check">
                                                            <input type="checkbox" 
                                                                   class="form-check-input doctor-checkbox" 
                                                                   name="medicos[]" 
                                                                   value="<?php echo $medico['id']; ?>" 
                                                                   id="doctor_<?php echo $medico['id']; ?>">
                                                            <label class="form-check-label" for="doctor_<?php echo $medico['id']; ?>">
                                                                <?php echo htmlspecialchars($medico['nombre']); ?>
                                                            </label>
                                                        </div>
                                                        <div class="lab-options ms-3">
                                                            <div class="btn-group btn-group-sm" role="group">
                                                                <input type="radio" 
                                                                       class="btn-check" 
                                                                       name="tipo_lab_<?php echo $medico['id']; ?>" 
                                                                       value="LS" 
                                                                       id="ls_<?php echo $medico['id']; ?>"
                                                                       disabled>
                                                                <label class="btn btn-outline-primary" 
                                                                       for="ls_<?php echo $medico['id']; ?>">LS</label>

                                                                <input type="radio" 
                                                                       class="btn-check" 
                                                                       name="tipo_lab_<?php echo $medico['id']; ?>" 
                                                                       value="LG" 
                                                                       id="lg_<?php echo $medico['id']; ?>"
                                                                       disabled>
                                                                <label class="btn btn-outline-primary" 
                                                                       for="lg_<?php echo $medico['id']; ?>">LG</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                                <?php endwhile; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" form="groupForm" class="btn btn-primary">Guardar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Manejar la selección de días
            $('.day-item').click(function() {
                $(this).toggleClass('selected');
                updateHiddenDays();
            });

            // Función para actualizar el campo oculto de días
            window.updateHiddenDays = function() {
                var selectedDays = $('.day-item.selected').map(function() {
                    return $(this).data('day');
                }).get();
                $('#dias_semana').val(selectedDays.join(','));
            }

            // Habilitar/deshabilitar opciones de laboratorio cuando se selecciona un médico
            $('.doctor-checkbox').change(function() {
                const medicoId = $(this).val();
                const labOptions = $(`input[name="tipo_lab_${medicoId}"]`);
                labOptions.prop('disabled', !this.checked);
            });

            // Manejar el envío del formulario
            $('#groupForm').on('submit', function(e) {
                e.preventDefault();
                
                // Recopilar datos de médicos
                const medicosData = [];
                $('.doctor-checkbox:checked').each(function() {
                    const medicoId = $(this).val();
                    const tipoLab = $(`input[name="tipo_lab_${medicoId}"]:checked`).val() || '';
                    medicosData.push({
                        id: medicoId,
                        tipo_lab: tipoLab
                    });
                });

                // Agregar datos al formulario
                const medicosDataInput = $('<input>')
                    .attr('type', 'hidden')
                    .attr('name', 'medicos_data')
                    .val(JSON.stringify(medicosData));
                
                $(this).append(medicosDataInput);

                // Enviar el formulario
                $.ajax({
                    url: $(this).attr('action'),
                    method: 'POST',
                    data: $(this).serialize(),
                    success: function(response) {
                        // Cerrar el modal
                        const modal = bootstrap.Modal.getInstance(document.getElementById('groupModal'));
                        if (modal) {
                            modal.hide();
                        }
                        // Recargar la página
                        window.location.reload();
                    },
                    error: function() {
                        alert('Hubo un error al procesar la solicitud. Por favor, intente nuevamente.');
                    }
                });
            });

            // Resto del código existente...
        });

        // Función para editar grupo
        function editarGrupo(button) {
            try {
                // Obtener y parsear los datos del grupo
                const grupoData = button.getAttribute('data-grupo');
                console.log('Datos del grupo (raw):', grupoData);
                const grupo = JSON.parse(grupoData);
                console.log('Datos del grupo (parsed):', grupo);

                // Resetear el formulario
                const form = document.getElementById('groupForm');
                form.reset();

                // Cambiar título y acción del formulario
                document.getElementById('modalTitle').textContent = 'Editar Grupo';
                document.querySelector('input[name="action"]').value = 'edit_group';
                
                // Establecer valores básicos del formulario
                document.getElementById('grupo_id').value = grupo.id;
                document.getElementById('nombre').value = grupo.nombre;
                document.getElementById('descripcion').value = grupo.descripcion || '';
                document.getElementById('sucursal_id').value = grupo.sucursal_id;
                
                // Establecer horarios
                if (grupo.hora_inicio) {
                    document.getElementById('hora_inicio').value = grupo.hora_inicio.substring(0, 5);
                }
                if (grupo.hora_fin) {
                    document.getElementById('hora_fin').value = grupo.hora_fin.substring(0, 5);
                }

                // Limpiar y establecer días seleccionados
                document.querySelectorAll('.day-item').forEach(item => {
                    item.classList.remove('selected');
                });
                
                if (grupo.dias_semana) {
                    const dias = grupo.dias_semana.split(',').map(d => d.trim());
                    dias.forEach(dia => {
                        const dayItem = document.querySelector(`.day-item[data-day="${dia}"]`);
                        if (dayItem) {
                            dayItem.classList.add('selected');
                        }
                    });
                }
                updateHiddenDays();

                // Limpiar selecciones de médicos
                document.querySelectorAll('input[name="medicos[]"]').forEach(checkbox => {
                    checkbox.checked = false;
                });
                document.querySelectorAll('input[type="radio"]').forEach(radio => {
                    radio.disabled = true;
                    radio.checked = false;
                });

                // Establecer médicos seleccionados
                if (grupo.medicos) {
                    const medicosAsignados = grupo.medicos.split('|');
                    medicosAsignados.forEach(medico => {
                        const [medicoId, nombre] = medico.split(':');
                        const checkbox = document.querySelector(`input[name="medicos[]"][value="${medicoId}"]`);
                        if (checkbox) {
                            checkbox.checked = true;
                            // Habilitar opciones de laboratorio
                            document.querySelectorAll(`input[name="tipo_lab_${medicoId}"]`).forEach(radio => {
                                radio.disabled = false;
                            });
                        }
                    });
                }

                // Mostrar el modal
                const modal = new bootstrap.Modal(document.getElementById('groupModal'));
                modal.show();
            } catch (error) {
                console.error('Error al editar grupo:', error);
                console.error('Stack trace:', error.stack);
                alert('Hubo un error al intentar editar el grupo. Por favor, intente nuevamente.');
            }
        }

        // Función para eliminar grupo
        function eliminarGrupo(id) {
            if (confirm('¿Está seguro de que desea eliminar este grupo?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_group">
                    <input type="hidden" name="grupo_id" value="${id}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
    </script>
</body>
</html>
