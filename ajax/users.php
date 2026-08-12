<?php
// ajax/users.php
header('Content-Type: application/json');

require_once '../models/User.php';

session_start();

$user = new User();
$action = $_POST['action'] ?? '';

switch ($action) {
    
    case 'checkSession':
        if (isset($_SESSION['usuario'])) {
            echo json_encode([
                'success' => true,
                'user' => $_SESSION['usuario']
            ]);
        } else {
            echo json_encode(['success' => false]);
        }
        break;
    
    case 'list':
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        echo json_encode($user->getAll());
        break;
    
    case 'get':
        if (!isset($_SESSION['usuario'])) {
            http_response_code(401);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        $id = (int)$_POST['id'];
        $result = $user->getById($id);
        if ($result) {
            echo json_encode($result);
        } else {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        }
        break;
    
    case 'create':
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        if (empty($_POST['id_rol']) || empty($_POST['nombre']) || empty($_POST['apellido_paterno']) || 
            empty($_POST['apellido_materno']) || empty($_POST['usuario']) || empty($_POST['contrasena'])) {
            echo json_encode(['success' => false, 'message' => 'Todos los campos obligatorios deben estar completos']);
            break;
        }
        
        if ($_POST['id_rol'] == 2 && empty($_POST['id_carrera'])) {
            echo json_encode(['success' => false, 'message' => 'Los coordinadores deben tener una carrera asignada']);
            break;
        }
        
        $result = $user->create($_POST);
        echo json_encode(['success' => $result]);
        break;
    
    case 'update':
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        
        $id = (int)$_POST['id'];
        
        if ($_POST['id_rol'] == 2 && empty($_POST['id_carrera'])) {
            echo json_encode(['success' => false, 'message' => 'Los coordinadores deben tener una carrera asignada']);
            break;
        }
        
        $result = $user->update($id, $_POST);
        echo json_encode(['success' => $result]);
        break;
    
    case 'delete':
        if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['id_rol'] > 2) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            break;
        }
        $id = (int)$_POST['id'];
        
        // No permitir eliminar el propio usuario
        if ($id == $_SESSION['usuario']['id_usuario']) {
            echo json_encode(['success' => false, 'message' => 'No puedes eliminar tu propia cuenta']);
            break;
        }
        
        $result = $user->delete($id);
        echo json_encode(['success' => $result]);
        break;
    
    case 'login':
        $usuario = trim($_POST['usuario'] ?? '');
        $password = $_POST['password'] ?? '';
        
        if (empty($usuario) || empty($password)) {
            echo json_encode([
                'success' => false,
                'message' => 'Debe ingresar usuario y contraseña.'
            ]);
            exit;
        }
        
        $login = $user->login($usuario, $password);
        
        if ($login) {
            $_SESSION['usuario'] = $login;
            echo json_encode([
                'success' => true,
                'rol' => $login['id_rol'],
                'user' => $login
            ]);
        } else {
            echo json_encode([
                'success' => false,
                'message' => 'Usuario o contraseña incorrectos.'
            ]);
        }
        break;
    
    default:
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => 'Acción inválida.'
        ]);
}