<?php
// ajax/grupos.php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../models/Grupo.php';

session_start();

if (!isset($_SESSION['usuario'])) {
    http_response_code(401);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

if ($_SESSION['usuario']['id_rol'] > 2) {
    http_response_code(403);
    echo json_encode(['success' => false, 'message' => 'No autorizado']);
    exit;
}

$grupo = new Grupo();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $idCarrera = $_POST['id_carrera'] ?? null;
            $result = $grupo->getAll($idCarrera);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'get':
        try {
            $id = (int)$_POST['id'];
            $result = $grupo->getById($id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'create':
        try {
            if (empty($_POST['nombre_grupo']) || empty($_POST['id_carrera'])) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                break;
            }
            
            $result = $grupo->create($_POST);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'update':
        try {
            $id = (int)$_POST['id'];
            unset($_POST['id']);
            unset($_POST['action']);
            
            if (empty($_POST['nombre_grupo']) || empty($_POST['id_carrera'])) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                break;
            }
            
            $result = $grupo->update($id, $_POST);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'delete':
        try {
            $id = (int)$_POST['id'];
            $result = $grupo->delete($id);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida: ' . $action]);
}