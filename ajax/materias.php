<?php
// ajax/materias.php
header('Content-Type: application/json');
require_once '../models/Materia.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$materia = new Materia();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        echo json_encode($materia->getAll());
        break;
    case 'get':
        echo json_encode($materia->getById((int)$_POST['id']));
        break;
    case 'create':
        echo json_encode(['success' => $materia->create($_POST)]);
        break;
    case 'update':
        echo json_encode(['success' => $materia->update((int)$_POST['id'], $_POST)]);
        break;
    case 'delete':
        echo json_encode(['success' => $materia->delete((int)$_POST['id'])]);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}