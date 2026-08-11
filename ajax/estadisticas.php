<?php
// ajax/estadisticas.php
header('Content-Type: application/json');
require_once '../includes/connection.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$db = Database::getConnection();
$filtros = $_POST['filtros'] ?? [];

// Construir filtros
$where = [];
$params = [];
$types = '';

if (!empty($filtros['id_carrera'])) {
    $where[] = "a.id_carrera = ?";
    $params[] = $filtros['id_carrera'];
    $types .= 'i';
}

if (!empty($filtros['id_semestre'])) {
    $where[] = "g.id_semestre = ?";
    $params[] = $filtros['id_semestre'];
    $types .= 'i';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

// Estadísticas básicas
$stats = [];

// Total estudiantes
$sql = "SELECT COUNT(*) as total FROM alumnos_unam a $whereClause";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stats['totalEstudiantes'] = $stmt->get_result()->fetch_assoc()['total'];

// Promedio general
$sql = "
    SELECT AVG(c.promediofinal) as promedio 
    FROM calificaciones_unam c
    INNER JOIN alumnos_unam a ON c.id_alumno = a.id_alumno
    $whereClause
";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['promedioGeneral'] = $result['promedio'] ?? 0;

// Tasa de aprobación
$sql = "
    SELECT 
        COUNT(CASE WHEN c.promediofinal >= 6 THEN 1 END) as aprobados,
        COUNT(*) as total
    FROM calificaciones_unam c
    INNER JOIN alumnos_unam a ON c.id_alumno = a.id_alumno
    $whereClause
";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$result = $stmt->get_result()->fetch_assoc();
$stats['tasaAprobacion'] = $result['total'] > 0 ? ($result['aprobados'] / $result['total']) * 100 : 0;
$stats['tasaReprobacion'] = 100 - $stats['tasaAprobacion'];

// Datos para gráficos
// Materias
$sql = "
    SELECT m.nombre_materia, AVG(c.promediofinal) as promedio
    FROM calificaciones_unam c
    INNER JOIN alumnos_unam a ON c.id_alumno = a.id_alumno
    INNER JOIN asignacion_maestros am ON c.id_asignacion = am.id_asignacion
    INNER JOIN materias m ON am.id_materia = m.id_materia
    $whereClause
    GROUP BY m.id_materia
    ORDER BY promedio DESC
    LIMIT 10
";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$materias = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stats['materiasLabels'] = array_column($materias, 'nombre_materia');
$stats['materiasData'] = array_column($materias, 'promedio');

// Carreras
$sql = "
    SELECT c.nombre_carrera, COUNT(a.id_alumno) as total
    FROM alumnos_unam a
    INNER JOIN carrera_unam c ON a.id_carrera = c.id_carrera
    $whereClause
    GROUP BY c.id_carrera
";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$carreras = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stats['carrerasLabels'] = array_column($carreras, 'nombre_carrera');
$stats['carrerasData'] = array_column($carreras, 'total');

// Rangos de calificación
$sql = "
    SELECT 
        COUNT(CASE WHEN c.promediofinal < 5 THEN 1 END) as rango1,
        COUNT(CASE WHEN c.promediofinal >= 5 AND c.promediofinal < 7 THEN 1 END) as rango2,
        COUNT(CASE WHEN c.promediofinal >= 7 AND c.promediofinal < 9 THEN 1 END) as rango3,
        COUNT(CASE WHEN c.promediofinal >= 9 THEN 1 END) as rango4
    FROM calificaciones_unam c
    INNER JOIN alumnos_unam a ON c.id_alumno = a.id_alumno
    $whereClause
";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$rangos = $stmt->get_result()->fetch_assoc();
$stats['rangosData'] = [
    $rangos['rango1'] ?? 0,
    $rangos['rango2'] ?? 0,
    $rangos['rango3'] ?? 0,
    $rangos['rango4'] ?? 0
];

// Top alumnos
$sql = "
    SELECT a.nombre, a.apellido_paterno, AVG(c.promediofinal) as promedio
    FROM calificaciones_unam c
    INNER JOIN alumnos_unam a ON c.id_alumno = a.id_alumno
    $whereClause
    GROUP BY a.id_alumno
    ORDER BY promedio DESC
    LIMIT 10
";
$stmt = $db->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param($types, ...$params);
}
$stmt->execute();
$stats['topAlumnos'] = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

echo json_encode($stats);