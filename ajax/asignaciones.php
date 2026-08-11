<?php
// ajax/asignaciones.php
header('Content-Type: application/json');
require_once '../models/Asignacion.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$asignacion = new Asignacion();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        echo json_encode($asignacion->getAll());
        break;
    case 'create':
        echo json_encode(['success' => $asignacion->create($_POST)]);
        break;
    case 'delete':
        echo json_encode(['success' => $asignacion->delete((int)$_POST['id'])]);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}