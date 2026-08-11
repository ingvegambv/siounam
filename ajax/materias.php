<?php
// ajax/materias.php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../models/Materia.php';

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

$materia = new Materia();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $result = $materia->getAll();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'get':
        try {
            $id = (int)$_POST['id'];
            $result = $materia->getById($id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'create':
        try {
            if (empty($_POST['nombre_materia']) || empty($_POST['id_carrera']) || empty($_POST['id_semestre'])) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                break;
            }
            
            $result = $materia->create($_POST);
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
            
            if (empty($_POST['nombre_materia']) || empty($_POST['id_carrera']) || empty($_POST['id_semestre'])) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                break;
            }
            
            $result = $materia->update($id, $_POST);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'delete':
        try {
            $id = (int)$_POST['id'];
            $result = $materia->delete($id);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida: ' . $action]);
}