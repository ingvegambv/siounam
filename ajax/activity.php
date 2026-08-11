<?php
// ajax/activity.php
header('Content-Type: application/json');
require_once '../includes/connection.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

// Simular actividad reciente basada en datos existentes
$db = Database::getConnection();
$actividad = [];

// Últimos usuarios registrados
$result = $db->query("
    SELECT CONCAT(nombre, ' ', apellido_paterno) as nombre, 
           'Se registró como usuario' as accion
    FROM usuarios_unam 
    ORDER BY id_usuario DESC 
    LIMIT 3
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $actividad[] = $row['nombre'] . ' - ' . $row['accion'];
    }
}

// Últimos alumnos registrados
$result = $db->query("
    SELECT CONCAT(nombre, ' ', apellido_paterno) as nombre,
           'Se registró como alumno' as accion
    FROM alumnos_unam 
    ORDER BY id_alumno DESC 
    LIMIT 3
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $actividad[] = $row['nombre'] . ' - ' . $row['accion'];
    }
}

// Si no hay actividad, mostrar mensaje
if (empty($actividad)) {
    $actividad = ['No hay actividad reciente en el sistema'];
}

echo json_encode($actividad);