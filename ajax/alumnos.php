<?php
// ajax/alumnos.php
header('Content-Type: application/json');
require_once '../models/Alumno.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$alumno = new Alumno();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        echo json_encode($alumno->getAll());
        break;
    case 'get':
        echo json_encode($alumno->getById((int)$_POST['id']));
        break;
    case 'create':
        echo json_encode(['success' => $alumno->create($_POST)]);
        break;
    case 'update':
        echo json_encode(['success' => $alumno->update((int)$_POST['id'], $_POST)]);
        break;
    case 'delete':
        echo json_encode(['success' => $alumno->delete((int)$_POST['id'])]);
        break;
    case 'migrar':
        $result = $alumno->migrar($_POST['id_grupo_origen'], $_POST['id_grupo_destino']);
        echo json_encode($result);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}