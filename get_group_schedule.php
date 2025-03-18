<?php
require_once "config/database.php";

header('Content-Type: application/json');

if(!isset($_GET['grupo_id'])) {
    echo json_encode(['error' => 'ID de grupo no proporcionado']);
    exit;
}

$grupo_id = $_GET['grupo_id'];

$sql = "SELECT * FROM horarios_grupo WHERE grupo_id = ? ORDER BY FIELD(dia_semana, 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'), hora_inicio";
$stmt = mysqli_prepare($conn, $sql);
mysqli_stmt_bind_param($stmt, "i", $grupo_id);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$horarios = [];
while($row = mysqli_fetch_assoc($result)) {
    $horarios[] = $row;
}

echo json_encode($horarios);
