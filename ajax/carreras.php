<?php
// ajax/carreras.php
header('Content-Type: application/json');
require_once '../models/Carrera.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$carrera = new Carrera();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        echo json_encode($carrera->getAll());
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida']);
}