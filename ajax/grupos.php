<?php
// ajax/grupos.php
header('Content-Type: application/json');
require_once '../models/Grupo.php';

session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$grupo = new Grupo();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        $idCarrera = $_POST['id_carrera'] ?? null;
        echo json_encode($grupo->getAll($idCarrera));
        break;
    case 'create':
        echo json_encode(['success' => $grupo->create($_POST)]);
        break;
    case 'update':
        echo json_encode(['success' => $grupo->update((int)$_POST['id'], $_POST)]);
        break;
    case 'delete':
        echo json_encode(['success' => $grupo->delete((int)$_POST['id'])]);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}