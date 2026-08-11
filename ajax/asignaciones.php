<?php
// ajax/asignaciones.php
header('Content-Type: application/json');

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../models/Asignacion.php';

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

$asignacion = new Asignacion();
$action = $_POST['action'] ?? '';

switch ($action) {
    case 'list':
        try {
            $result = $asignacion->getAll();
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'get':
        try {
            $id = (int)$_POST['id'];
            $result = $asignacion->getById($id);
            echo json_encode($result);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'create':
        try {
            if (empty($_POST['id_grupounam']) || empty($_POST['id_materia']) || empty($_POST['id_usuario'])) {
                echo json_encode(['success' => false, 'message' => 'Todos los campos son obligatorios']);
                break;
            }
            
            $checkUser = $asignacion->verificarMaestro((int)$_POST['id_usuario']);
            if (!$checkUser) {
                echo json_encode(['success' => false, 'message' => 'El usuario seleccionado no es un maestro válido']);
                break;
            }
            
            $checkGrupo = $asignacion->verificarGrupo((int)$_POST['id_grupounam']);
            if (!$checkGrupo) {
                echo json_encode(['success' => false, 'message' => 'El grupo seleccionado no existe']);
                break;
            }
            
            $checkMateria = $asignacion->verificarMateria((int)$_POST['id_materia']);
            if (!$checkMateria) {
                echo json_encode(['success' => false, 'message' => 'La materia seleccionada no existe']);
                break;
            }
            
            $duplicado = $asignacion->verificarDuplicado(
                (int)$_POST['id_grupounam'],
                (int)$_POST['id_materia'],
                (int)$_POST['id_usuario']
            );
            
            if ($duplicado) {
                echo json_encode(['success' => false, 'message' => 'Esta asignación ya existe']);
                break;
            }
            
            $result = $asignacion->create($_POST);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    case 'delete':
        try {
            $id = (int)$_POST['id'];
            $result = $asignacion->delete($id);
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;

    case 'delete_all':
        try {
            $result = $asignacion->deleteAll();
            echo json_encode(['success' => $result]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Acción inválida: ' . $action]);
}