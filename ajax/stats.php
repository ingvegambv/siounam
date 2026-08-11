<?php
// ajax/stats.php
header('Content-Type: application/json');
require_once '../includes/connection.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false]);
    exit;
}

$db = Database::getConnection();

$stats = [];

// Total usuarios activos
$result = $db->query("SELECT COUNT(*) as total FROM usuarios_unam WHERE activo = 1");
$stats['totalUsuarios'] = $result->fetch_assoc()['total'] ?? 0;

// Total alumnos
$result = $db->query("SELECT COUNT(*) as total FROM alumnos_unam");
$stats['totalAlumnos'] = $result->fetch_assoc()['total'] ?? 0;

// Total materias
$result = $db->query("SELECT COUNT(*) as total FROM materias");
$stats['totalMaterias'] = $result->fetch_assoc()['total'] ?? 0;

// Total grupos
$result = $db->query("SELECT COUNT(*) as total FROM grupo_unam");
$stats['totalGrupos'] = $result->fetch_assoc()['total'] ?? 0;

// Obtener distribución por carrera (si existe la relación)
$carrerasData = [];
$result = $db->query("
    SELECT c.nombre_carrera, COUNT(a.id_alumno) as total
    FROM alumnos_unam a
    LEFT JOIN carrera_unam c ON a.id_carrera = c.id_carrera
    GROUP BY a.id_carrera
");
if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $carrerasData[] = [
            'label' => $row['nombre_carrera'] ?? 'Sin carrera',
            'value' => (int)$row['total']
        ];
    }
}
$stats['carreras'] = $carrerasData;

// Generar evolución basada en el total de alumnos
$totalAlumnos = $stats['totalAlumnos'];
$evolucionData = [];
$meses = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];

if ($totalAlumnos > 0) {
    // Distribuir el total entre los meses de forma progresiva
    $acumulado = 0;
    for ($i = 0; $i < 12; $i++) {
        // Simular crecimiento: los primeros meses menos, últimos más
        $factor = ($i + 1) / 12;
        $valor = round($totalAlumnos * $factor * 0.8) + round($totalAlumnos * 0.2 * ($i / 12));
        $acumulado = max($acumulado, $valor);
        $evolucionData[] = [
            'label' => $meses[$i],
            'value' => $acumulado
        ];
    }
} else {
    // Si no hay alumnos, mostrar datos de ejemplo con 0
    for ($i = 0; $i < 12; $i++) {
        $evolucionData[] = [
            'label' => $meses[$i],
            'value' => 0
        ];
    }
}
$stats['evolucion'] = $evolucionData;

echo json_encode($stats);